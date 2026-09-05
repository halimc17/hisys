<?php
ini_set('display_errors',0);error_reporting(0);
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$validasiPostMobile = explode(" ", $_POST['par']);
	if($validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}
// echo"<pre>";
// print_r($_GET);
// print_r($_POST);
// exit("error");

require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method             = checkPostGet('method', '');
$param              = $_POST;
$tipe               = checkPostGet('tipe', '');
$jenis              = checkPostGet('jenis', '');
$notransaksi        = checkPostGet('notransaksi', '');
$rupiah             = checkPostGet('rupiah', '');
$rupiah             = str_replace(',','',$rupiah);
$keterangan         = checkPostGet('keterangan', '');
$nourut             = checkPostGet('nourut', '');
$row                = checkPostGet('row', '');
$pt                 = checkPostGet('pt', '');
$unit               = checkPostGet('unit', '');
$kategori           = checkPostGet('kategori', '');
$tanggalsurat       = tanggalsystemn(checkPostGet('tanggalsurat', ''));
$tanggaldari        = tanggalsystemn(checkPostGet('tanggaldari', ''));
$tanggalsampai      = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$tanggal            = tanggalsystemn(checkPostGet('tanggal', ''));
$divisi             = checkPostGet('divisi', '');
$bagian             = checkPostGet('bagian', '');
$project            = checkPostGet('project', '');
$koderekanan        = checkPostGet('koderekanan', '');
$perjanjianinduk    = checkPostGet('perjanjianinduk', '');
$perjanjianperubahan= checkPostGet('perjanjianperubahan', '');
$retensi            = checkPostGet('retensi', '');
$denda              = checkPostGet('denda', '');
$jangkawaktu        = checkPostGet('jangkawaktu', '');
$garansi            = checkPostGet('garansi', '');
$namafile           = checkPostGet('namafile', '');
$divsch             = checkPostGet('divsch', '');
$jenissch           = checkPostGet('jenissch', '');
$nohaksch           = checkPostGet('nohaksch', '');
$unitsch            = checkPostGet('unitsch', '');
$projectsch         = checkPostGet('projectsch', '');
$koderekanansch     = checkPostGet('koderekanansch', '');
$statussch          = checkPostGet('statussch', '');
$subunit            = checkPostGet('subunit', '');
$kegiatan           = checkPostGet('kegiatan', '');
$total              = checkPostGet('total', '');
$hk                 = checkPostGet('hk', '');
$volume             = checkPostGet('volume', '');
$satuan             = checkPostGet('satuan', '');
$kepada             = checkPostGet('kepada', '');
$numrow             = checkPostGet('numrow', '');
$rptermin           = checkPostGet('rptermin', '');
$jenissupplier      = checkPostGet('jenissupplier', '');
$nopol              = checkPostGet('nopol', '');
$tipeangkut         = checkPostGet('tipeangkut', '');
$pendukung          = checkPostGet('pendukung', '');
$alasanclose        = checkPostGet('alasanclose', '');
$jenisupload        = checkPostGet('jenisupload', '');
$rupiahttl        	= checkPostGet('rupiahttl', '');
$jlhhm        		= checkPostGet('jlhhm', '');
$notransaksiold     = checkPostGet('notransaksiold', '');
$rptermin           = str_replace(',','',$rptermin);
$rupiahttl          = str_replace(',','',$rupiahttl);
$param['nilaipajak']= str_replace(',','',$param['nilaipajak']);
$volume             = str_replace(',','',$volume);
$total              = str_replace(',','',$total);
$path               = "fileupload/lgl_pengajuanspk/";
$today              = date('Y-m-d');
$todayhis           = date('Y-m-d H:i:s');
$spesifikasi        = trim(checkPostGet('spesifikasi', ''));

$kriteriaefil = checkPostGet('kriteriaefil', '0');
$urlefil = checkPostGet('urlefil', '0');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmakun= makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
$emodul = "SPK";
$jnsupspkfinal='ESPKF';

switch ($method) {
	case'jumlahhari':
			$date1 = $tanggaldari;
			$date2 = $tanggalsampai;
			$a = datediff($date1, $date2);
			echo @$a[years]." tahun, ".@$a[months]." bulan, ".@$a[days]." hari";
		break;
		case'getnotransaksi':
			#001/EXT/LGL/BOD/BJHO/IX/2017
			$tempPrd=explode('-',$tanggalsurat);
			$str=" select notransaksi from ".$dbname.".lgl_pengajuanspkht where pt='".$pt."' and unit='".$unit."' and tanggal like '".$tempPrd[0]."%' order by notransaksi desc limit 1 "; //exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$tempNo1=explode('/',$bar['notransaksi']);
			if(intval($bar['notransaksi'])==0 or intval($bar['notransaksi'])==999){
				$nomorsurat = "001";
			}else{
				$nomorsurat = addZero(intval($tempNo1[0])+1,3);
			}
			$_SESSION['pajak']=array();
			$_SESSION['nopol']=array();
	echo $nomorsurat."/SPK/".$pt."/".$unit."/".romawi($tempPrd[1])."/".$tempPrd[0];
	break;
	case'getjenis':
		$optjns="<option value=''></option>";
		$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','jenis');
		foreach( $arrtipe as $key => $val){
			
			if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
				if($val=='HOLDING'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
				if($val=='KANWIL'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'KEBUN'){
				if($val=='KEBUN'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'PABRIK'){
				if($val=='PABRIK' and $val!='BELITBS'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}


			if($val!='PABRIK' and $val!='HOLDING' and $val!='KANWIL' and $val!='KEBUN' and $val!='BELITBS'){
				$optjns.="<option value=".$val.">".$val."</option>";
			}
		}
	echo $optjns;
	break;
	case'getunit':
	$where = $whp = ''; $isi = '';
	if ($jenis != '') {
		if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
			if ($jenis =='KANWIL' or $jenis =='KEBUN' or $jenis =='PABRIK') {
				$where.=" and tipe ='".$jenis."'";
			}else{
				$where.=" and tipe in ('PABRIK','KEBUN','KANWIL','HOLDING')";
			}
		} else {
			if ($jenis =='KANWIL' or $jenis =='KEBUN' or $jenis =='PABRIK') {
				$where.=" and tipe ='".$jenis."' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
			}else{
				#$where.=" and tipe = '".$_SESSION['empl']['tipelokasitugas']."'";
				$where.=" and kodeorganisasi in (".getOrgDetail(2).")";
			}
		}
	}

	#exit("error".$isi);
	$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc "; #exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$sel='';
		if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
			#$sel=" selected ";
		}
		$optun.="<option ".$sel." value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optun;
	break; 
	case'getdivisi':
	$optdiv=$optspk="<option value=''></option>";
	if($jenis=='PO/SO'){
		$str="select a.nopo from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where b.stat_release='1' and a.spk='1' and b.nopo not in (select divisi from ".$dbname.".lgl_pengajuanspkht where jenis='PO/SO' and statuspersetujuan!='3')";
		// $str="select * from ".$dbname.".log_prapo_vw where kodeorg='".$pt."' and nopp like '%".$unit."%' and close='2' and nopp not in (select distinct(nopp) from log_podt) order by nopp asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['nopo'].">".$bar['nopo']."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project where kodeorg='".$unit."' and posting='0' and pekerjaan='External'  order by kode asc"; 
		$count=fetchData($str);
		if(count($count)<=0){
			// exit('Warning : Silahkan buat project terlebih dahulu !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
		}
		// $optdiv="<option value='PROJECT'>Project</option>";
	}else{
		$wh='';
		if($jenis=='KEBUN'){
			$wh.=" and (tipe ='AFDELING' and induk='".$unit."') or (kodeorganisasi='".$unit."') ";
		}else if($jenis=='PABRIK'){
			$wh.=" and tipe ='STATION' and induk='".$unit."'";
		}else{
			$wh.=" and kodeorganisasi='".$unit."'";
		}
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." order by namaorganisasi asc "; #exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
	}
	echo $optdiv;
	break;
	case'loaddatapajak':

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'loaddatanopol':
		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'deletenopol':
		unset($_SESSION['nopol'][$param['no']]);

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;
	case'addnopol':

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		if(!preg_match("/^[a-zA-Z0-9]*$/", $param['nopol'])){
			exit("Warning : Hanya boleh Huruf dan Angka.");
		}
		
		if(is_numeric(substr($param['nopol'],0,1))){
			exit("error : No polisi salah, lengkapi kembali No polisi.");
		}

		$newdata = array();
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'nopol'      =>$param['nopol'],
			'supir'      =>$param['supir']
		);
		if($_SESSION['nopol'] != array()){
			foreach($_SESSION['nopol'] as $key=>$row){
				if($row['notransaksi'] == $param['notransaksi'] && $row['nopol'] == $param['nopol']){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			if($newdata['nopol']!=''){
				array_push($_SESSION['nopol'],$newdata);
			}
		}else{
			if($newdata['nopol']!=''){
				array_push($_SESSION['nopol'],$newdata);
			}
		}

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($_SESSION['nopol']);
		// print_r($param);
		// echo"</pre>";

		echo $tab;
	break;
	case'deletepajak':
		unset($_SESSION['pajak'][$param['no']]);

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'addpajak':

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		$newdata = array();
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'tipe'       =>'pajak',
			'nourut'     =>$param['jenispajak'],
			'nilai'      =>$param['nilaipajak']
		);
		if($_SESSION['pajak'] != array()){
			foreach($_SESSION['pajak'] as $key=>$row){
				if($row['notransaksi'] == $param['notransaksi'] && $row['nourut'] == $param['jenispajak']){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			if($newdata['nourut']!='' and $newdata['nilai']!=''){
				array_push($_SESSION['pajak'],$newdata);
			}
		}else{
			if($newdata['nourut']!='' and $newdata['nilai']!=''){
				array_push($_SESSION['pajak'],$newdata);
			}
		}

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($_SESSION['pajak']);
		// print_r($param);
		// echo"</pre>";

		echo $tab;
	break;
	case'getsubunit':

	$namaproject = "x";
	$tgldari = "x";
	$tglsampai = "x";
	$supplierid = "x";
	$totalrupiah = "x";
	$kodecapex = "x";
	if($notransaksi!=''){
		$sSub="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$rSub=fetchData($sSub);
		$isiHt=$rSub[0];
	}

	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$optkeg="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PO/SO'){
		$str="select * from ".$dbname.".log_podt where nopo='".$divisi."' order by nopo asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$opt.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".$nmbrg[$bar['kodebarang']]."</option>";
			$optkeg.="<option value=".$bar['catatan'].">".$bar['catatan']."</option>";
		}
	}else if($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project_dt where kodeproject='".$divisi."'"; //exit('error'.$str);
		$count=fetchData($str);
		if(count($count)<=0){
			// exit('Warning : Tidak ada detail project !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkeg.="<option value=".$bar['kegiatan'].">".$bar['namakegiatan']."</option>";
		}
		$nmpro=makeOption($dbname,'project','kode,nama',"kode='".$divisi."'");
		$opt.="<option value=".$divisi.">".$nmpro[$divisi]."</option>";
		// $opt.="<option value='Project'>Project</option>";

		##GET DETAIL PROJECT
		$str="select * from ".$dbname.".project where kode='".$divisi."'";
		$res=fetchData($str);
		$namaproject = $res[0]['nama'];
		$tgldari = $res[0]['tanggalmulai'];
		$tglsampai = $res[0]['tanggalselesai'];
		$kodecapex = $res[0]['kodecapex'];

		if($kodecapex!=''){
			$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kodecapex."'";
			$res=fetchData($str);
			$supplierid = $res[0]['kontraktor'];

			$str="select * from ".$dbname.".spl_tendercapex where kode='".$kodecapex."' and supplierid='".$supplierid."'";
			$res=fetchData($str);
			$idtender = $res[0]['id'];

			$str="select * from ".$dbname.".spl_tendercapexdt where tendercapexid='".$idtender."'";
			$res=fetchData($str);
			$totalrupiah = 0;
			foreach($res as $key=>$val){
				$optVolume = makeOption($dbname,'spl_capexbangunandt','kegiatan,volume',"kegiatan='".addZero($val['kodekegiatan'],8)."'");
				$volume = $optVolume[addZero($val['kodekegiatan'],8)];

				if($val['tipedata']=='kegiatan'){
					$totalrupiah = $totalrupiah + (($volume*$val['hargasatuan']) + ($val['hk']*$val['rphk']));
				}

				if($val['tipedata']=='material'){
					$totalrupiah = $totalrupiah + (($val['jumlah']*$val['hargasatuan']));
				}
			}
		}
	}else{
		$wh='';
		$kol='namaorganisasi,kodeorganisasi,induk';
		if($divisi!=''){
			if($jenis=='KEBUN'){
				$wh.=" and indukblok like '".$divisi."%'";
				$kol = "distinct indukblok,namaindukblok";
			}else if($jenis=='PABRIK'){
				$wh.=" and induk='".$divisi."'";
			}else{
				if($jenis=='SEWA.HM'){
					$wh.=" and kodeorganisasi like '".$divisi."%'";
				}else{					
					$wh.=" and kodeorganisasi='".$divisi."'";
				}
			}
		}else{
			if($jenis=='KEBUN'){
				$wh.=" and indukblok like '".$unit."%' and indukblok in (select indukblok from ".$dbname.".setup_blok where (luasareaproduktif > 0 OR lc > 0 OR luasbloking > 0) and status='A')";
			}else if($jenis=='PABRIK'){
				$wh.=" and induk='".$unit."'";
			}else{
				$wh.=" and kodeorganisasi='".$unit."'";
			}
		}
		if($jenis=='SEWA.HM'){
			$str="select * from ".$dbname.".vhc_5master where 1=1 and kodetraksi like '".$unit."%' and kepemilikan='0' and status='1'"; #exit('error'.$str);
			$res = fetchdata($str);
			$opt.="<optgroup label='KENDARAAN'>";
			foreach($res as $bar){
				$sttdt="";
				if($isiHt['divisi']==$bar['kodevhc']){
					$sttdt="selected";
				}
				$opt.="<option value=".$bar['kodevhc']." ".$sttdt.">".$bar['kodevhc']." - ".$bar['nopol']." (".$bar['detailvhc'].")</option>";
			}
			$opt.="</optgroup>";
			
			$str="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where 1=1 ".$wh." and tipe not like '%GUDANG%'  order by induk, namaorganisasi asc "; #exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
			while($bar=$res->fetch()){
				$d=$bar['induk'];
				if ($d !== $n && $n !== "") {
					$opt .= "</optgroup>";
				}
				if($d!=$n){			
					$opt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				$sttdt="";
				if($isiHt['divisi']==$bar['kodeorganisasi']){
					//$sttdt="selected";
				}
				$opt.="<option value=".$bar['kodeorganisasi']." ".$sttdt.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				
				$n=$d;
				if($d!=$n){			
					$opt.="</optgroup>";
				}
			}
		}else{			
			$str="select ".$kol." from ".$dbname.".organisasi where 1=1 ".$wh." and tipe not like '%GUDANG%'  order by induk, namaorganisasi asc "; #exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
			while($bar=$res->fetch()){
				$d=$induk[$bar['induk']];
				if ($d !== $n && $n !== "") {
					$opt .= "</optgroup>";
				}
				if($d!=$n){			
					$opt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				$sttdt="";
				if($isiHt['divisi']==$bar['kodeorganisasi']){
					$sttdt="selected";
				}

				if($jenis != 'KEBUN'){
					$opt.="<option value=".$bar['kodeorganisasi']." ".$sttdt.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}else{
					$opt.="<option value=".$bar['indukblok']." ".$sttdt.">".$bar['indukblok']." - ".$bar['namaindukblok']."</option>";
				}
				$n=$d;
				if($d!=$n){			
					$opt.="</optgroup>";
				}
			}
		}
		
		$whr='';
		if($jenis=='KEBUN' and $divisi!=''){
			$whr.=" and kelompok in ('BBT','PNN','TB','TBM','TM','LC')";
		}elseif($jenis=='KEBUN' and $divisi==''){
			$whr.=" and kelompok in ('KNT')";
		}elseif($jenis=='SEWA.HM' and $divisi!=''){
			$whr.=" and kelompok in ('TRK','BBT','PNN','TB','TBM','TM','LC')";
		}elseif($jenis=='ANGKUTTBS' and $divisi!=''){
			$whr.=" and kelompok in ('PNN')";
		}else{
			$whr.=" and left(kelompok,3) in ('MIL','KNT')";
		}
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." order by kodekegiatan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
		while($bar=$res->fetch()){
			$d=substr($bar['kodekegiatan'],0,3);
			if ($d !== $n && $n !== "") {
				$optkeg .= "</optgroup>";
			}
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}			
		}
	}
	
	$strspk="select notransaksi from ".$dbname.".lgl_pengajuanspkht where jenis = '".$jenis."' and unit='".$unit."'";
	//echo $strspk;
	$rtrcust=fetchData($strspk);
	foreach ($rtrcust as $key => $val) {
		$optspk.="<option value=".$val['notransaksi'].">".$val['notransaksi']."</option>";
	}
	
	echo $opt."####".$optkeg."####".$namaproject."####".tanggalnormal($tgldari)."####".tanggalnormal($tglsampai)."####".$totalrupiah."####".$supplierid."####".$kodecapex;
	break;

	case'getsatuan':
	$opt=$optkeg="";
	$optkeg="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PO/SO'){
		$opt=makeOption($dbname,'log_5masterbarang','satuan,satuan',"kodebarang='".$subunit."'");
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$opt=makeOption($dbname,'project_dt','satuan,satuan',"kegiatan='".$kegiatan."'");
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";
		}
	}elseif($jenis=='SEWA.HM'){
		$kend = makeOption($dbname,'vhc_5master','kodevhc,kodevhc',"kodevhc='".$subunit."'");
		$whr="";
		if($kend[$subunit]!=''){
			$whr.=" and kelompok in ('TRK')";
		}elseif(strlen($subunit)<10){
			$whr.="";
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='PABRIK'){
				$whr.=" and noakun like '7%'";
			}
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='KEBUN'){
				$whr.="";
			}
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='TC'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='RND'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='KANWIL'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='BULKING'){
				$whr.=" and noakun like '81%'";
			}
		}else{
			if(getBlok($subunit,'statusblok')=='TM'){
				$whr.=" and kelompok in ('TM','PNN')";
			}else{				
				$whr.=" and kelompok in ('".getBlok($subunit,'statusblok')."')";
			}
		}
		
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." order by kodekegiatan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
		while($bar=$res->fetch()){
			$d=substr($bar['kodekegiatan'],0,3);
			if ($d !== $n && $n !== "") {
				$optkeg .= "</optgroup>";
			}
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}			
		}
		
		$whr='';
		$whr.=" and kodekegiatan ='".$kegiatan."'";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
		}
	}else{
		$whr='';
		$whr.=" and kodekegiatan ='".$kegiatan."'";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
		}
	}
	echo $optsat."##".$optkeg;
	break;



	case'html':
	$tab=$brd='';
	if($tipe=='html'){
		#$tab= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"viewexcel('".$notransaksi."','excel');\">";
		$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
	}
	
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}  
		
	$countApprove = getCountApproval('SPK',$unit);
	$str=" select * from ".$dbname.".lgl_pengajuanspkht where  notransaksi='".$notransaksi."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();

	if($bar['kategori']=='PUSAT'){
		$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar['pt']."' and tipe='HOLDING'";
		$resx=fetchdata($strx);
		$bar['unit'] = $resx[0]['kodeorganisasi'];
	}else{
		$bar['unit'] = $bar['unit'];
	}
	if($tipe != 'pdf'){

		$tab.= "
			<table border=0 cellspacing=1 cellpadding=5 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
		$tab.= "
			</tr>
			</thead>
			<tbody>";
			$tab.= "<tr class=rowcontent>
					<td>".$nmkar[$bar['updateby']]."<br>
						".waktunormal($bar['createtime'])."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='SPK' and level='".$i."' and kodeunit='".$bar['unit']."'";
					$resx=fetchData($strx);
					$tipeapp = $resx[0]['tipe'];
					$departemenapp = $resx[0]['departemen'];
					$tipekaryawanapp = $resx[0]['tipekaryawan'];
					$jabatanapp = $resx[0]['jabatan'];
	
					$arrApp = detailApprove($i,$notransaksi,'SPK');
	
					if($tipeapp=='1' && $arrApp['status']!=''){
						if($arrApp['status']!='1'){
							if($departemenapp!=''){
								$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
								$arrApp['nama'] = $opttipe[$departemenapp];
							}
	
							if($tipekaryawanapp!=''){
								$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
								$arrApp['nama'] = $opttipe[$tipekaryawanapp];
							}
	
							if($jabatanapp!='0'){
								$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
								$arrApp['nama'] = $opttipe[$jabatanapp];
							}
						}
					}
	
					if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
						$tngl='';
					}else{
						$tngl=tanggalnormal($arrApp['tanggal']);
					}
	
					if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
						$tab.= "<td>".$arrApp['nama']."
							<br />".$arrHsl[$arrApp['status']]."
							<br />".$tngl."
							<br />".$arrApp['komentar']."
						</td>";
					}else{
						$tab.= "<td>&nbsp;</td>";
					}
				}
	
	
			$tab.= "</tbody>
			</table><div style=clear:both><br></div>";
	}


	$nmrekanan=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
	$nmklsup=makeOption($dbname,'log_5klsupplier','tipe,kode');

	$no = 0;
	$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$jenis = $bar['jenis'];
	$rp='';
	$strx = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkdt	 where notransaksi='".$notransaksi."'";
	$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_ASSOC);
	$groupArr = array();
	while($barx = $resx->fetch()){
		$d['nourut']= $barx['nourut'];
		$d['tipe']  = $barx['tipe'];
		$d['nilai'] = $barx['nilai'];
		$d['keterangan'] = $barx['keterangan'];
		$groupArr[] = $d;
	}
	if($tipe=='html'){
		$tab.= "<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
	} elseif ($tipe == 'pdf') {
		$brd = " style='border:0.5px solid black;margin:0 auto!important;'";
		$tab.= "<h3 align=center>Pengajuan SPK</h3>";
		$tab.= "<table cellpadding=1.5 cellspacing=0 ".$brd.">";
	} else{
		$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
	}
	$nmsupprekanan=$nmrekanan[$bar['koderekanan']];
	if($nmsupprekanan==""){
		$optnmcust=makeOption($dbname,"pmn_4customer","kodecustomer,namacustomer");
		$nmsupprekanan=$optnmcust[$bar['koderekanan']];
	}
	$tab.= "<tr class=rowcontent>
			<td ".$brd.">Nomor</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4 id=notranpopup>".$notransaksi."</td>
			<td ".$brd.">Kategori</td>
			<td ".$brd.">:</td>
			<td ".$brd.">".$bar['kategori']."</td>
			<td ".$brd.">Jenis</td>
			<td ".$brd.">:</td>
			<td ".$brd.">".$bar['jenis']."</td>
			<td ".$brd.">Tanggal</td>
			<td ".$brd.">:</td>
			<td ".$brd."  colspan=4>".$bar['tanggal']."</td>
		</tr>
		<tr class=rowcontent>
			<td ".$brd.">" . $_SESSION['lang']['pt'] . "</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$nmorg[$bar['pt']]."</td>
			<td ".$brd.">" . $_SESSION['lang']['unit'] . "</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$nmorg[$bar['unit']]."</td>
			<td ".$brd.">" . $_SESSION['lang']['divisi'] . "</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['divisi']."</td>
		</tr>
		<tr class=rowcontent>
			<td style=display:none ".$brd." >" . $_SESSION['lang']['bagian'] . "</td>
			<td style=display:none ".$brd." >:</td>
			<td style=display:none ".$brd."  colspan=4>".$bar['bagian']."</td>
			<td ".$brd.">" . $_SESSION['lang']['project'] . "</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['project']."</td>
			<td ".$brd.">" . $_SESSION['lang']['koderekanan'] . "</td>
			<td ".$brd.">:</td>
			<td ".$brd."  colspan=4>".$nmsupprekanan."</td>
			<td ".$brd.">".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['supplier']."</td>
			<td ".$brd.">:</td>
			<td ".$brd."  colspan=4>".$nmklsup[$bar['jenissupplier']]."</td>
		</tr>
		<tr class=rowcontent>
			<td style=display:none ".$brd." >Perjanjian Induk</td>
			<td style=display:none ".$brd." >:</td>
			<td style=display:none ".$brd."  colspan=4>".$bar['perjanjianinduk']."</td>
			<td style=display:none ".$brd." >Perjanjian Perubahan</td>
			<td style=display:none ".$brd." >:</td>
			<td style=display:none ".$brd."  colspan=4>".$bar['perjanjianperubahan']."</td>

			<td ".$brd.">Tanggal</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['tanggaldari']." s/d ".$bar['tanggalsampai']."</td>
			<td ".$brd.">Jangka Waktu</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['jangkawaktu']."</td>

			<td ".$brd.">Retensi (%)</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['retensi']."</td>
		</tr>
		<tr style=display:none class=rowcontent>
			<td ".$brd.">Denda</td>
			<td ".$brd.">:</td>
			<td ".$brd.">".$bar['denda']."</td>
			<td ".$brd.">Garansi</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['garansi']."</td>
		</tr>
		<tr class=rowcontent>
			<td ".$brd.">SPK Lama</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4>".$bar['notransaksiold']."</td>
			<td ".$brd.">Jumlah HM</td>
			<td ".$brd.">:</td>
			<td ".$brd." colspan=4 align=right>".$bar['jlhhm']."</td>
			<td ".$brd."  valign=top>No Polisi</td>
			<td ".$brd."  valign=top>:</td>
			<td ".$brd."  valign=top colspan=4 rowspan=3><div style=overflow:auto;height:100px>
		</tr>
		<tr class=rowcontent>
			<td ".$brd." valign=top>Total Nilai Kontrak (Rp)</td>
			<td ".$brd." valign=top>:</td>
			<td ".$brd." colspan=4 valign=top >";
		$tab.="<table  width=100%>";
		foreach($groupArr as $key => $val){
			if($val['tipe']=='rupiah'){
				$tab.="<tr>";
				$tab.="<td ".$brd." align=left>".hidezerodecimal($val['nilai'],2)."</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		$tab.="</td>
			<td ".$brd." valign=top>Pajak (%)</td>
			<td ".$brd." valign=top>:</td>
			<td ".$brd." valign=top colspan=6>";
		$tab.="<table  width=100%>";
		foreach($groupArr as $key => $val){
			if($val['tipe']=='pajak'){
				$tab.="<tr>";
				$tab.="<td ".$brd.">".$val['nourut']." ".$nmakun[$val['nourut']]."</td>";
				$tab.="<td ".$brd."> = </td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($val['nilai'],2)." %</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		$tab.="</td>";
		if($bar['jenis']=='ANGKUTTBS'){
			$tab.="<table width=100% border=0 cellpadding=3 cellspacing=1 class=sortable>";
			$tab.="<thead><tr class=rowheader>";
			$tab.="<td ".$brd.">" . $_SESSION['lang']['nopol'] . "</td>";
			$tab.="<td ".$brd.">" . $_SESSION['lang']['sopir'] . "</td>";
			$tab.="<td ".$brd." width=30px>" . $_SESSION['lang']['status'] . "</td>";
			$tab.="<td ".$brd." width=20px>" . $_SESSION['lang']['action'] . "</td>";
			$tab.="</tr></thead>";

			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$brd." style=\"width:80px\"><input id=nopol2 class=myinputtext maxlength=9 onkeydown=\"upperCaseF(this)\" style=\"width:80px;align:left;\"></td>";
			$tab.="<td ".$brd."><input id=supir2 class=myinputtext placeholder='Sopir' onkeydown=\"upperCaseF(this)\" style=\"width:99%;align:left;\"></td>";
			$opt.="<option value='A'>Aktif</option><option value='D'>Nonaktif</option>";
			$tab.="<td ".$brd." width=30px><select style=\"width:50px;\" id=status2>".$opt."</select>
					</td>";
			$tab.="<td ".$brd." width=20px align=center><img src='images/plus.png' class='zImgBtn' title='Tambah Nopol'; onclick=addnopol2('".$notransaksi."'); style='position:relative;top:3px;left:3px;'></td>";
			$tab.="</tr>";

			$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $notransaksi . "'";
			$res = fetchdata($str);
			foreach($res as $val){
				$tab.="<tr  class=rowcontent>";
				$tab.="<td ".$brd.">".$val['nopol']."</td>";
				$tab.="<td ".$brd.">".$val['supir']."</td>";
				if($val['status']=='A'){$n="Aktif";}else{$n='Nonaktif';}
				$tab.="<td ".$brd.">".$n."</td>";
				$tab.="<td ".$brd." align=center><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editnopol('".$val['nopol']."','".$val['supir']."','".$val['status']."')\"></td>";
				$tab.="</tr>";
			}
			$tab.="</table>";
		}
		$tab.="</div></td>
		</tr>
		<tr class=rowcontent>
			<td ".$brd." valign=top>Spesifikasi<br>Pekerjaan</td>
			<td ".$brd." valign=top>:</td>
			<td ".$brd." valign=top colspan=12>".nl2br($bar['spesifikasi'])."</td>
		</tr>";
		$tab.="</table>
		<div style=clear:both><br></div>
		<table class='sortable' cellspacing='1' cellpadding=5 border='0' >
			<thead>
			<tr class=rowheader>
				<th align=center ".$brd.">No</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['subunit'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['kegiatan'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['satuan'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['hk'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['volume'] . "</th>
				<th align=center ".$brd.">Rp / Sat</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['total'] . "</th>
			</tr>
			</thead>
			<tbody>";

		$no = 0;
		$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspk_keg	 where 1=1 and notransaksi='" . $notransaksi . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td ".$brd." colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkeg='';
			while ($bar = $res->fetch()) {
				$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					//$xx.=" style=background-color:#F5EEF8";
				}
				$namakegiatan = '';
				## CEK PROJECT
				$strx="select * from ".$dbname.".project where kode='".$bar['subunit']."'";
				$resx=fetchData($strx);
				$namaproject = $resx[0]['nama'];
				$kodecapex = $resx[0]['kodecapex'];

				if($kodecapex==''){
					$strx="select * from ".$dbname.".project_dt where kegiatan='".$bar['kegiatan']."'";
					$resx=fetchData($strx);
					$namakegiatan = $resx[0]['namakegiatan'];
				}else{
					$strx="select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$bar['kegiatan']."'";
					$resx=fetchData($strx);
					$namakegiatan = $resx[0]['namakegiatan'];
				}
				$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['subunit']."'");
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td ".$brd." align=center>" . $no . "</td>";
				if($jenis=='SEWA.HM' and $nmorg[$bar['subunit']]==''){
					$tab.="<td ".$brd.">" . $bar['subunit'] . " - " .getNopol($bar['subunit']). "</td>";
				}else{					
					$tab.="<td ".$brd.">" . $bar['subunit'] . " - " .($nmorg[$bar['subunit']]==''?($namaproject==''?$optNamaBarang[$bar['subunit']]:$namaproject):$nmorg[$bar['subunit']]). "</td>";
				}
				$tab.="<td ".$brd.">" . $bar['kegiatan'] . " - " . ($nmkeg[$bar['kegiatan']]==''?$namakegiatan:$nmkeg[$bar['kegiatan']]) . "</td>";
				$tab.="<td ".$brd." align=center>" . $bar['satuan'] . "</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['hk'])."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['volume'],2)."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal(@($bar['total']/$bar['volume']),2)."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['total'])."</td>";
				$tab.="</tr>";

				$ttl+=$bar['total'];
			}

			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$brd." align=center colspan=7>TOTAL</td>";
			$tab.="<td ".$brd." align=right>".hidezerodecimal($ttl)."</td>";
			$tab.="</tr>";
		}

		$tab.="</tbody>
		</table>";
	if($tipe=='html'){
		$namafile=checkPostGet('namafile','');
		if($namafile!=''){
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			if (file_exists($namafile)){
				unlink($namafile);
			}
			file_put_contents($namafile, $dompdf->output());
		}else{			
			echo $tab;
			echo @$isi.="<div style=clear:both><br></div><table class='sortable'  cellpadding=5 cellspacing='1' border='0'>
						<thead>
						<tr class=rowheader>
							<td align='center' width=50px>No.</td>
							<td align='center' width=50px>File Type</td>
							<td align='center' width=100px>Kriteria</td>
							<td align='center'>Filename</td>
							<td align='center' width=50px>Action</td>
						</tr>
						</thead>
						<tbody id='loadfilesdetail'>
						</tbody>
					</table>";
		}
	}else if($tipe =='pdf'){
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream("Realisasi BAPP",array("Attachment"=>0));
	}else {
		$stream = $tab;
		$nop_ = "pengajuan_spk";
		if (strlen($stream) > 0) {
			if($urlefil=='0'){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							 @ unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/".$nop_.".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				closedir($handle);
			}else{
				$handle=fopen($urlefil, 'w');
				fwrite($handle, $stream);
				fclose($handle);
			}
		}
	}
	break;
	case'addnopol2':
		if(!preg_match("/^[a-zA-Z0-9]*$/", $param['nopol'])){
			exit("Warningcode : Hanya boleh Huruf dan Angka.");
		}
		
		if(is_numeric(substr($param['nopol'],0,1))){
			exit("errorcode : No polisi salah, lengkapi kembali No polisi.");
		}
		
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $notransaksi . "' and nopol='".$param['nopol']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str="update ".$dbname.".log_spknopol set supir='".$param['supir']."', status='".$param['stat']."' where notransaksi='".$notransaksi."' and nopol='".$param['nopol']."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}else{
			if($param['nopol']=='' and $param['supir']==''){
				exit("errorcode : No polisi dan sopir tidak boleh kosong.");
			}
			
			$data = array(
				'notransaksi'=> $notransaksi,
				'nopol'      => $param['nopol'],
				'supir'      => $param['supir'],
				'status'      => $param['stat'],
				'createby'   => $_SESSION['standard']['userid'],
				'createdate' => date("YmdHis")
			);

			$cols = array();
			foreach($data as $keyn=>$rown) {
					$cols[] = $keyn;
			}
			$str = insertQuery($dbname,'log_spknopol',$data,$cols);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;
	case'viewlistfile':
	$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center' width=750px>Kriteria</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
	echo $tab;
	break;
	// case'editdetail':
		// $rupiah=$pajak=$termin=$no='';
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res);
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan'];
			// }else{
				// $rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan']."#$#";
			// }
		// }
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='pajak'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res); $no='';
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai'];
			// }else{
				// $pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai']."#$$#";
			// }
		// }
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res); $no='';
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $termin.=$bar['tipe']."######".$bar['nourut']."######".number_format($bar['nilai'],2)."######".$bar['keterangan']."######".$bar['rptermin'];
			// }else{
				// $termin.=$bar['tipe']."######".$bar['nourut']."######".number_format($bar['nilai'],2)."######".$bar['keterangan']."######".$bar['rptermin']."#$$$#";
			// }
		// }
		// echo $rupiah."########".$pajak."########".$termin;
	// break;
	case'insertdetail':
	try {
		$owlPDO->beginTransaction();

		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		if(count($res)==''){
			exit("Warning : Silahkan simpan Header terlebih dahulu !");
		}

		$str = "insert into " . $dbname . ".lgl_pengajuanspk_keg (`notransaksi`,`subunit`,`kegiatan`,`satuan`,`volume`,`total`,`hk`)
		values ('".$notransaksi."','".$subunit."','".$kegiatan."','".$satuan."','".$volume."','".$total."','".$hk."')";//exit('error'.$str);
		$owlPDO->exec($str);
		

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];
		
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			#update total rupiah
			$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
			$owlPDO->exec($str);			
		}else{
			$str = "insert into " . $dbname . ".lgl_pengajuanspkdt (`notransaksi`,`tipe`,`nourut`,`nilai`,`rptermin`,`keterangan`)
			values ('".$notransaksi."','rupiah','1','".$totalrupiah."','','')";//exit('error'.$str);
			$owlPDO->exec($str);
		}
		

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
		
		echo $totalrupiah;
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'editdetail2':
	try {
		$owlPDO->beginTransaction();

		$hk = str_replace(',','',$hk);

		$str="update ".$dbname.".lgl_pengajuanspk_keg set total='".$total."',hk='".$hk."' where notransaksi='".$notransaksi."' and subunit='".$subunit."' and kegiatan='".$kegiatan."'";
		// echo $str;
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];

		#update total rupiah
		$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

		echo $totalrupiah;
	break;
	case'deldetail':
	try {
		$owlPDO->beginTransaction();

		$str = "delete from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."' and subunit='".$subunit."'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];

		#update total rupiah
		$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
		echo $totalrupiah;
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

	break;
	// case'insertdt':
	// try {
		// $owlPDO->beginTransaction();
		// # Delete dulu
		// if($row==1){
			// $str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='".$jenis."'";
			// $owlPDO->exec($str);
			// #try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		// }
		// # Jika data sudah ada maka langsung Insert
		// //exit('error'.$rupiah);
		// //if($rupiah!=''){
			// $str = "insert into " . $dbname . ".lgl_pengajuanspkdt (`notransaksi`,`tipe`,`nourut`,`nilai`,`keterangan`,`rptermin`)
			// values ('".$notransaksi."','".$jenis."','".$nourut."','".floatval($rupiah)."','".$keterangan."','".floatval($rptermin)."')"; #exit('error'.$str);
			// $owlPDO->exec($str);
			// #try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		// //}

			// $owlPDO->commit();
		// } catch (PDOException $e) {
			// $owlPDO->rollback();
			// echo "Error, " . addslashes($e->getMessage());
			// die();
		// }
	// break;
	case'insert':
	try {
		$owlPDO->beginTransaction();

		$tempdt = array();
		$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val){
			$tempdt[$no]['notransaksi'] = $val['notransaksi'];
			$tempdt[$no]['subunit'] = $val['subunit'];
			$tempdt[$no]['kegiatan'] = $val['kegiatan'];
			$tempdt[$no]['satuan'] = $val['satuan'];
			$tempdt[$no]['volume'] = $val['volume'];
			$tempdt[$no]['total'] = $val['total'];
			$tempdt[$no]['hk'] = $val['hk'];
			$no++;
		}
		if($jenis!='JUALTBS'){
			if($divisi=='') {
				throw new PDOException("Divisi ".$_SESSION['lang']['kosong']);
			}
			if($jenis=='') {
				throw new PDOException("Jenis Supplier ".$_SESSION['lang']['kosong']);
			}
			
			$scek="select * from ".$dbname.".pmn_4customer where kodecustomer='".$koderekanan."'";
			$rcek=fetchData($scek);
			if(count($rcek)>0){
				throw new PDOException("".$_SESSION['lang']['koderekanan']." harus terisi Supplier, karena jenis :".$jenis);
			}
		}else{
			$scek="select * from ".$dbname.".pmn_4customer where kodecustomer='".$koderekanan."'";
			$rcek=fetchData($scek);
			if(count($rcek)==0){
				throw new PDOException("".$_SESSION['lang']['koderekanan']." harus terisi customer, karena jenis :".$jenis);
			}
		}

		if($jenissupplier==''){
			throw new PDOException("Jenis Asignment wajib diisi.");
		}

		# Delete dulu
		$str = "delete from " . $dbname . ".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);


		# Jika data sudah ada maka langsung Insert
		$str = "insert into " . $dbname . ".lgl_pengajuanspkht (`notransaksi`,`kategori`,`jenis`,`pt`,`unit`,`divisi`,`bagian`,`tanggal`,`koderekanan`,`perjanjianinduk`,`perjanjianperubahan`,`project`,`spesifikasi`,`jangkawaktu`,`tanggaldari`,`tanggalsampai`,`garansi`,`retensi`,`denda`,`statuspersetujuan`,`createby`,`createtime`,`updateby`,`jenissupplier`,`pendukung`,`notransaksiold`,`jlhhm`)
		values ('".$notransaksi."','".$kategori."','".$jenis."','".$pt."','".$unit."','".$divisi."','".$bagian."','".$tanggal."','".$koderekanan."','".$perjanjianinduk."','".$perjanjianperubahan."','".$project."','".$spesifikasi."','".$jangkawaktu."','".$tanggaldari."','".$tanggalsampai."','".$garansi."','".$retensi."','".$denda."','0','" . $_SESSION['standard']['userid'] . "','".$todayhis."','" . $_SESSION['standard']['userid'] . "','".$jenissupplier."','".$pendukung."','".$notransaksiold."','".$jlhhm."')";
		$owlPDO->exec($str);


		if($tempdt!=array()){
			foreach($tempdt as $key=>$val){
				$str="insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total,hk)
					values ('".$notransaksi."','".$val['subunit']."','".$val['kegiatan']."','".$val['satuan']."','".$val['volume']."','".$val['total']."','".$val['hk']."')";
				$owlPDO->exec($str);
			}
		}else{
			if($jenis=='PROJECT'){
				$str="select * from ".$dbname.".project where kode='".$divisi."'";
				$res=fetchData($str);
				$kodecapex = $res[0]['kodecapex'];
				$subunit = $res[0]['kode'];

				if($kodecapex!=''){
					$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kodecapex."'";
					$res=fetchData($str);
					$supplierid = $res[0]['kontraktor'];

					$str="select * from ".$dbname.".spl_tendercapex where kode='".$kodecapex."' and supplierid='".$supplierid."'";
					$res=fetchData($str);
					$idtender = $res[0]['id'];

					$str="select * from ".$dbname.".spl_tendercapexdt where tendercapexid='".$idtender."' order by tipedata asc";
					$res=fetchData($str);
					foreach($res as $key=>$val){
						$kegiatan = addZero($val['kodekegiatan'],8);
						$strx = "select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$kegiatan."'";
						$resx = fetchData($strx);
						$satuan = $resx[0]['satuan'];
						if($val['tipedata']=='kegiatan'){
							$volume = $resx[0]['volume'];
							$total = round($volume*$val['hargasatuan'],2) + round($val['hk']*$val['rphk'],2);
						}else{
							$volume = $val['jumlah'];
							$total = round($volume*$val['hargasatuan'],2);
						}

						if($val['tipedata']=='kegiatan'){
							$strx = "insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total,hk)
							values ('".$notransaksi."','".$subunit."','".$kegiatan."','".$satuan."','".$volume."','".$total."','".$val['hk']."')";
							$owlPDO->exec($strx);
						}else{
							$strx="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."'";
							$resx=fetchData($strx);
							$temptotal = $resx[0]['total'];
							$valtotal = $total + $temptotal;
							$strx="update ".$dbname.".lgl_pengajuanspk_keg set total='".$valtotal."' where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."'";
							$owlPDO->exec($strx);
						}
					}
				}else{
					$str="select * from ".$dbname.".project_dt where kodeproject='".$divisi."'";
					$res=fetchData($str);
					foreach($res as $key=>$val){
						$strx = "insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total)
						values ('".$notransaksi."','".$val['kodeproject']."','".$val['kegiatan']."','".$val['satuan']."','".$val['volume']."','0')";
						$owlPDO->exec($strx);
					}
				}
			}
		}



		if($rupiahttl!=0){
			$str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah'";
			$data = array(
				'notransaksi'=> $notransaksi,
				'tipe'       => 'rupiah',
				'nourut'     => '1',
				'nilai'      => $rupiahttl
			);

			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'lgl_pengajuanspkdt',$data,$cols);
			$owlPDO->exec($str);
		}

		// echo"<pre>";
		// print_r($_SESSION['pajak']);
		// echo"</pre>";
		if($_SESSION['pajak'] != array()){
			$str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='pajak'";
			$owlPDO->exec($str);

			$datapajak=array();
			foreach($_SESSION['pajak'] as $key=>$row){
				if($row['notransaksi'] == $notransaksi){
					$datapajak = array(
						'notransaksi'=> $notransaksi,
						'tipe'       => $row['tipe'],
						'nourut'     => $row['nourut'],
						'nilai'      => $row['nilai']
					);

					$colspajak = array();
					foreach($datapajak as $keyn=>$rown) {
							$colspajak[] = $keyn;
					}
					$str = insertQuery($dbname,'lgl_pengajuanspkdt',$datapajak,$colspajak);
					$owlPDO->exec($str);

					#unset($_SESSION['pajak'][$key]);
				}
			}
		}

		if($_SESSION['nopol'] != array()){
			$str = "delete from " . $dbname . ".log_spknopol where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);

			$data=array();
			foreach($_SESSION['nopol'] as $key=>$row){
				if($row['notransaksi'] == $notransaksi){
					$data = array(
						'notransaksi'=> $notransaksi,
						'nopol'      => $row['nopol'],
						'supir'      => $row['supir'],
						'createby'   => $_SESSION['standard']['userid'],
						'createdate' => $todayhis
					);

					$cols = array();
					foreach($data as $keyn=>$rown) {
							$cols[] = $keyn;
					}
					$str = insertQuery($dbname,'log_spknopol',$data,$cols);
					$owlPDO->exec($str);

					#unset($_SESSION['nopol'][$key]);
				}
			}
		}
		$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];
		// exit('warning'.$totalrupiah);
		echo $totalrupiah;
	break;
	case'delete':
	$str = "delete from " . $dbname . ".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
	try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	# delete file
	$sql = "select * from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."'";
	$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."'and namafile='".$bar['namafile']."'";
		try{$owlPDO->exec($str);
			$pathx = $path.$bar['namafile'];
			unlink($pathx);
		}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
	}
	break;
	case'loaddata':

	$where = "";
	// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	// 	$where.="";
	// }elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	// 	$where.=" and pt = '".$_SESSION['empl']['kodeorganisasi']."'";
	// }else{
		$where.=" and unit in  (".getOrgDetail(2).")";
	// }

	if($projectsch!=''){
		$where.=" and project like '%" . $projectsch . "%' ";
	}
	if($koderekanansch!=''){
		$where.=" and koderekanan in (select supplierid from ".$dbname.".log_5supplier where namasupplier like '%".$koderekanansch."%') ";
	}
	if ($divsch != '') {
		$where.=" and pt='" . $divsch . "' ";
	}
	if ($jenissch != '') {
		$where.=" and jenis='" . $jenissch . "' ";
	}
	if ($unitsch != '') {
		$where.=" and unit='" . $unitsch . "' ";
	}
	if ($nohaksch != '') {
		$where.=" and notransaksi like '%" . $nohaksch . "%' ";
	}
	if ($statussch!='') {
		$where.=" and statuspersetujuan='".$statussch."' ";
	}

	$limit = 20;
	$page = 0;
	$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
	if (isset($_POST['page'])) {
		$page = $_POST['page'];
		if ($page < 0)
		$page = 0;
	}
	$offset = $page * $limit;
	$maxdisplay = ($page * $limit);
	$sql = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkht where 1=1 " . $where . "";
	$res = fetchData($sql);
	$jlhbrs = count($res);
	$no = 0;
	$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkht where 1=1 " . $where . " order by createtime desc limit " . $offset . "," . $limit . ""; //exit("error $str");
	$tab = "";
	$no = $maxdisplay;
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=20 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		while ($bar = $res->fetch()) {
			$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
			$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar['koderekanan']."'");
			$strx = "SELECT sum(nilai) as nilai FROM " . $dbname . ".lgl_pengajuanspkdt	 where notransaksi='".$bar['notransaksi']."' and tipe='rupiah'";$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','jenis');
			$resx = fetchData($strx);
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				//$xx.=" style=background-color:#F5EEF8 ";
			}
			
			$xxx='';
			if($bar['statuspersetujuan']==3){
				$xxx=" style=background-color:red ";
			}
			if($bar['statuspersetujuan']==1){
				$xxx=" style=background-color:green ";
			}
			if($bar['statuspersetujuan']==0){
				$xxx=" style=background-color:yellow ";
			}

			$sqlapp = "SELECT notransaksi FROM " . $dbname . ".approval where notransaksi='".$bar['notransaksi']."' and status='0'";
			$resapp = fetchData($sqlapp);

			## CEK SPK
			$sttSpk = "";
			$sttBapp = "";
			$optSpk = makeOption($dbname,'log_spkht','nopengajuan,notransaksi',"nopengajuan='".$bar['notransaksi']."'");
			if(@$optSpk[$bar['notransaksi']]!=''){
				$sttSpk = "v";
				$optBapp = makeOption($dbname,'log_baspk','notransaksi,notransaksi',"notransaksi='".$optSpk[$bar['notransaksi']]."'");
				if($optBapp[$optSpk[$bar['notransaksi']]]!=''){
					$sttBapp = "v";
				}
			}


			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td>" . $bar['pt'] ."</td>";
			$tab.="<td nowrap>" . $bar['unit'] . " - " . $nmorg[$bar['unit']] . "</td>";
			$tab.="<td>" . $bar['notransaksi'] . "</td>";
			$tab.="<td align=center nowrap>" . tanggalnormal($bar['tanggal']) . "</td>";
			if (@$nmsup[$bar['koderekanan']]!='') {
				$tab.="<td>" . @$nmsup[$bar['koderekanan']] . "</td>";
			}else{
				$tab.="<td>" . @$nmcus[$bar['koderekanan']] . "</td>";
			}
			$tab.="<td align=center>" . $arrtipe[$bar['jenis']] . "</td>";
			$tab.="<td>" . $bar['project'] . "</td>";
			#$tab.="<td>" . $bar['perjanjianinduk'] . "</td>";
			$tab.="<td align=right>".hidezerodecimal($resx[0]['nilai'])."</td>";

			if($bar['pendukung']==1){
				$e="Hanya Pendukung";$q="style=background-color:yellow";
			}else{
				$q=$e="";
			}

			$tab.="<td hidden align=center ".$q.">".$e."</td>";

			if($bar['close']==1){
				$e="\nCLOSE";$q="style=background-color:red";
			}else{
				$q=$e="";
			}
			
			if($bar['statuspersetujuan']==0 and $bar['posting']==0){
				$tab.="<td align=center nowrap>Belum diajukan".$e."</td>";
			}else{
				$tab.="<td align=center nowrap align=left ".$q." ".$xxx.">".$arrHsl[$bar['statuspersetujuan']]."".$e."</td>";
			}

			$tab.="<td align=center>".$sttSpk."</td>";
			$tab.="<td align=center style=cursor:pointer;color:blue;font-weight:bold; title=\"Click untuk melihat BAPP\" onclick=\"viewdetailbapp('".$optBapp[$optSpk[$bar['notransaksi']]]."','".$bar['unit']."','viewhtml','event')\">".$sttBapp."</td>";
			$tab.="<td align=left>" . $nmkar[$bar['updateby']] . "</td>";

			#ambil total
			$stri="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$bar['notransaksi']."'";
			$resi=fetchData($stri);
			if($resi[0]['total'] != 0 || $resi[0]['total'] == ''){
				$totalrupiah = $resi[0]['total'];
			}else{
				$totalrupiah = 0;
			}
			if($bar['posting']==0){
				$isi.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn	title='Edit'
				onclick=\"fillfield('".$bar['notransaksi']."');\" ></td>";

				$isi.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$bar['notransaksi']."');\" title='Delete'></td>";

				$isi.="<td align=center width=20px><img src=images/skyblue/submit.jpg class=zImgBtn class=zImgBtn height='30'  title='Ajukan ???'
				onclick=\"form_ajukan_spk('" . $bar['notransaksi'] . "','" . $bar['unit'] . "','" . $no . "','".$totalrupiah."');\" ></td>";
			}elseif($bar['close']==0 and $bar['statuspersetujuan']==1){
				$isi.="<td width=20px></td><td width=20px></td>
				<td  align=center width=20px><img src=images/icons/book_previous.png class=zImgBtn class=zImgBtn height='30'  title='Close ???'
				onclick=\"form_tutup('".$bar['notransaksi']."','".$bar['unit']."','".$no."');\" ></td>";
			}else{
				$isi.="<td width=20px></td><td width=20px></td>";
				$isi.="<td width=20px>".$bar['alasanclose']."</td>";
			}

			$isi.="<td align=center width=20px><img src=images/zoom.png class=zImgBtn	title='View' onclick=\"html('".$bar['notransaksi']."','html');\"></td>";
			$isi.="<td align=center width=20px><img src=images/pdf.jpg class=zImgBtn	title='View' onclick=\"html('".$bar['notransaksi']."','pdf');\"></td>";

			if ($optBapp[$optSpk[$bar['notransaksi']]]!='') {
				$isi.="<td align=center width=20px><img src=images/upload-2-xxl.png class=zImgBtn	title='Upload' onclick=\"showupload('event','".$bar['notransaksi']."','1');\"></td>";
			}else{
				$isi.="<td align=center  width=20px></td>";
			}

			$tab.=$isi;
			$tab.="</tr>";
		}
	}
	$totrows = ceil($jlhbrs / $limit);
	if ($totrows == 0) {
		$totrows = 1;
	}
	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++) {
		$sel = ($page == $er - 1) ? 'selected' : '';
		$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
	}
	$footd = createpaging($jlhbrs,$limit,$page,'19','loaddata','getPage');
	echo $tab . "####" . $footd;
	break;
	case'fillfield':
		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$hsl['notransaksi'] 		= $bar['notransaksi'];
			$hsl['kategori'] 			= $bar['kategori'];
			$hsl['jenis'] 				= $bar['jenis'];
			$hsl['pt'] 					= $bar['pt'];
			$hsl['unit'] 				= $bar['unit'];
			$hsl['divisi'] 				= $bar['divisi'];
			$hsl['bagian'] 				= $bar['bagian'];
			$hsl['tanggal'] 			= tanggalnormal($bar['tanggal']);
			$hsl['koderekanan']			= $bar['koderekanan'];
			$hsl['perjanjianinduk'] 	= $bar['perjanjianinduk'];
			$hsl['perjanjianperubahan'] = $bar['perjanjianperubahan'];
			$hsl['project'] 			= $bar['project'];
			$hsl['spesifikasi'] 		= $bar['spesifikasi'];
			$hsl['jangkawaktu'] 		= $bar['jangkawaktu'];
			$hsl['tanggaldari']			= tanggalnormal($bar['tanggaldari']);
			$hsl['tanggalsampai']		= tanggalnormal($bar['tanggalsampai']);
			$hsl['garansi']				= $bar['garansi'];
			$hsl['retensi']				= $bar['retensi'];
			$hsl['denda']				= $bar['denda'];
			$hsl['jenissupplier']		= $bar['jenissupplier'];
			$hsl['pendukung']			= $bar['pendukung'];
			$hsl['notransaksiold'] 		= $bar['notransaksiold'];
			$hsl['jlhhm']				= $bar['jlhhm'];
		}

		## Cek kategori jika orang HO tidak bisa edit SPK KEBUN, dan begitu juga kebalikan nya (Biar gak dikira bug)
		if($hsl['kategori'] == 'LOKAL' and $_SESSION['empl']['tipelokasitugas'] == 'HOLDING' ){
				exit("Warning: Lokasi tugas Holding tidak dapat edit SPK kategori Lokal ");
		}	

		if($hsl['kategori'] == 'PUSAT' and $_SESSION['empl']['tipelokasitugas'] != 'HOLDING' ){
				exit("Warning: Lokasi tugas Holding tidak dapat edit SPK kategori Lokal ");
		}	

		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$hsl['nilai']=number_format($bar['nilai'],2);
		}

		$_SESSION['pajak']=array();
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['tipe']=='pajak'){
				$_SESSION['pajak'][]=array(
					'notransaksi'=>$bar['notransaksi'],
					'tipe'       =>$bar['tipe'],
					'nourut'     =>$bar['nourut'],
					'nilai'      =>$bar['nilai']
				);
			}
		}

		$hsl['pajak']="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$hsl['pajak'].="<thead><tr class=rowheader style=text-align:center>";
		$hsl['pajak'].="<td>".$_SESSION['lang']['namaakun']."</td>";
		$hsl['pajak'].="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$hsl['pajak'].="<td width=18px>#</td>";
		$hsl['pajak'].="</tr>";
		$hsl['pajak'].="</thead>";
		$hsl['pajak'].="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$hsl['pajak'].="<tr class='rowcontent'>";
				$hsl['pajak'].="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$hsl['pajak'].="<td style='text-align:right'>".$row['nilai']."</td>";
				$hsl['pajak'].="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$hsl['pajak'].="</tr>";
			}
		}
		$hsl['pajak'].="</tbody>";
		$hsl['pajak'].="</table>";

		$_SESSION['nopol']=array();
		$str="select * from ".$dbname.".log_spknopol where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$_SESSION['nopol'][]=array(
				'notransaksi'=>$bar['notransaksi'],
				'nopol'      =>$bar['nopol'],
				'supir'      =>$bar['supir']
			);
		}


		$hsl['nopol']="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$hsl['nopol'].="<thead><tr class=rowheader style=text-align:center>";
		$hsl['nopol'].="<td>".$_SESSION['lang']['nopol']."</td>";
		$hsl['nopol'].="<td>".$_SESSION['lang']['sopir']."</td>";
		$hsl['nopol'].="<td width=18px>#</td>";
		$hsl['nopol'].="</tr>";
		$hsl['nopol'].="</thead>";
		$hsl['nopol'].="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$hsl['nopol'].="<tr class='rowcontent'>";
				$hsl['nopol'].="<td style='text-align:left'>".$row['nopol']."</td>";
				$hsl['nopol'].="<td style='text-align:left'>".$row['supir']."</td>";
				$hsl['nopol'].="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$hsl['nopol'].="</tr>";
			}
		}
		$hsl['nopol'].="</tbody>";
		$hsl['nopol'].="</table>";


		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$hsl['totalrupiah'] = $res[0]['total'];
		
		// echo $n1."##".$n2."##".$pajak."##".$tab."##".$totalrupiah;
		echo json_encode($hsl);
	break;
	case'form_tutup':
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<input hidden id=notran_tutup value=".$notransaksi.">
					<input hidden id=unit_tutup value=".$unit.">
					<td colspan=2><textarea rows='2' maxlength=1024 id=alasanclose type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:255px;\"></textarea></td>
				</tr>
				<tr class=rowcontent>
					<input id=numrow_tutup style=display:none value=".$numrow.">
					<td colspan=2 align=center>
					<button class=mybutton onclick=tutup()>" . $_SESSION['lang']['close'] . "</button></td>
				</tr>
				</table>";

        echo $tab;

	break;
	case'tutup':
		if($alasanclose==''){
			exit("Warning : Alasan Harus diisi !");
		}

		#cek apakah sudah ada di spk
		$str="select * from ".$dbname.".log_spkht where notransaksi='".$notransaksi."' and kodeorg='".$unit."'";
		$res=fetchdata($str);
		if(count($res)>0){
			#exit("Warning : Surat Perjanjian Kerja (SPK) sudah dibuat, Proses di Batalkan !");
		}

		#cek apakah sudah ada di bapp
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		if(count($res)>0){
			exit("Warning : BAPP sudah dibuat, Proses di Batalkan !");
		}

		$str="update ".$dbname.".log_spkht set posting='2' where notransaksi='".$notransaksi."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}

		$str = "update " . $dbname . ".lgl_pengajuanspkht set close='1',alasanclose='".$alasanclose."' where notransaksi = '" . $notransaksi . "'";
		try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	break;
	case'form_ajukan';
		$kodeorg=$unit;
		$str="select divisi,kategori,pt,unit from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$kategori = $res[0]['kategori'];
		$kdproject = $res[0]['divisi'];
		if($kategori=='PUSAT'){
			$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$res[0]['pt']."' and tipe='HOLDING'";
			$resx=fetchdata($strx);
			$kodeorg = $resx[0]['kodeorganisasi'];
		}else{
			$kodeorg = $res[0]['unit'];
		}

		## CEK KONDISI JIKA SUDAH MENGAJUKAN APPROVAL PROJECT
 
		if ($kdproject!='') { 
			$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".project_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."'  and a.level='1'  and kode='".$kdproject."' order by b.namakaryawan asc";
			$rescek=fetchdata($str);
			if(count($rescek)>0){
				$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".project_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."'  and a.level='1'  and kode='".$kdproject."' order by b.namakaryawan asc";
			}else{
				$str="select distinct a.karyawanid,a.nilaidari,a.nilaisampai,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SPK' and a.level='1' and a.kodeunit='".$unit."' order by b.namakaryawan asc"; 
			}
		}else{ 
			$str="select distinct a.karyawanid,a.nilaidari,a.nilaisampai,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SPK' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc";
		}

		//$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='SPK' and a.level='1' and a.kodeunit='".$kodeorg."'  order by b.namakaryawan asc"; ==> QUERY AWAL

		// exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			if($rkry['nilaidari']=='0' && $rkry['nilaisampai']=='0'){
				$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
			}else{
				if($rkry['nilaidari'] < $param['rupiahttl'] && ($param['rupiahttl'] < $rkry['nilaisampai'])){
					$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
				}
			}
		}

	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['notransaksi'] . "</td>
					<td width=5px>:</td>
					<td id=notran_aju>".$notransaksi."</td>
				</tr>

				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:99%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=left><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>
				</table>";

        echo $tab;
	break;
	case'getJenisSup':
		if($notransaksi!=''){
			$sSub="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
			$rSub=fetchData($sSub);
			$isiHt=$rSub[0];
		}
		$optjenis=$optspk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$strkel="select a.tipe,b.kode, b.noakun from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5klsupplier b on a.tipe=b.tipe where a.supplierid = '".$koderekanan."' and status='1'";
        // exit('warning : '.$strkel);
        $reskel=fetchData($strkel);
        foreach ($reskel as $key => $barkel) {
            if($isiHt['jenissupplier']==$barkel['tipe']){
                $optjenis.="<option value='".$barkel['tipe']."' selected>".$barkel['kode']." - ".getNamaAkun($barkel['noakun'])." (".$barkel['noakun'].")</option>";
            }else{
                $optjenis.="<option value='".$barkel['tipe']."'>".$barkel['kode']." - ".getNamaAkun($barkel['noakun'])." (".$barkel['noakun'].")</option>";
            }
        }
		
		$strspk="select notransaksi from ".$dbname.".lgl_pengajuanspkht where jenis = '".$jenis."'";
		//echo $strspk;
		$rtrcust=fetchData($strspk);
		foreach ($rtrcust as $key => $val) {
			$optspk.="<option value=".$val['notransaksi'].">".$val['notransaksi']."</option>";
		}
        echo $optjenis."##".$optspk;
	break;

    case'ajukan':

	#= cari unit
	$str = "select * from " . $dbname . ".lgl_pengajuanspkht  where notransaksi = '" . $notransaksi . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
		$jenis=$bar['jenis'];
		$pendukung=$bar['pendukung'];


	try {
	$owlPDO->beginTransaction();
		$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		if(($jenis!='HOLDING' and $jenis!='JUALTBS') and ($jenis!='ANGKUTTBS' && $jenis != 'SEWA.HM')){
			// if($pendukung!='1'){
				if(count($res)==''){
					throw new PDOException('Detail kegiatan tidak ada.');
				}
			// }
		}

		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".lgl_pengajuanspkht set posting='1' where notransaksi = '" . $notransaksi . "'";
		$owlPDO->exec($str);
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','SPK','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;

	case'loaddatadetail':
	$tab = "";
	$tab.= "<table  cellpadding=5 cellspacing=1 border=0 class=sortable>";
	$tab.= "<thead>";
	$tab.= "<tr class=rowheader>";
	$tab.= "<th align=center>No</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['subunit'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['satuan'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['hk'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['volume'] . "</th>";
	$tab.= "<th align=center>Rp / Sat</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
	$tab.= "<th align=center colspan=2 >Action</th>";
	$tab.= "</tr>";
	$tab.= "</thead>";

	$no = 0;
	$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspk_keg	 where 1=1 and notransaksi='" . $notransaksi . "'"; //exit("error $str");
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		$nmkeg='';
		while ($bar = $res->fetch()) {
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				#$xx.=" style=background-color:#F5EEF8";
			}
			$namakegiatan = '';
			## CEK PROJECT
			$strx="select * from ".$dbname.".project where kode='".$bar['subunit']."'";
			$resx=fetchData($strx);
			$namaproject = $resx[0]['nama'];
			$kodecapex = $resx[0]['kodecapex'];

			if($kodecapex==''){
				$strx="select * from ".$dbname.".project_dt where kegiatan='".$bar['kegiatan']."'";
				$resx=fetchData($strx);
				$namakegiatan = $resx[0]['namakegiatan'];
			}else{
				$strx="select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$bar['kegiatan']."'";
				$resx=fetchData($strx);
				$namakegiatan = $resx[0]['namakegiatan'];
			}

			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>" . $no . "</td>";
			if(getNopol($bar['subunit'],'kodevhc')!=''){
				$tab.="<td>" . $bar['subunit'] . " - " .getNopol($bar['subunit']). "</td>";
			}else{				
				$tab.="<td>" . $bar['subunit'] . " - " .($nmorg[$bar['subunit']]==''?$namaproject:$nmorg[$bar['subunit']]). "</td>";
			}
			
			$tab.="<td>" . $bar['kegiatan'] . " - " . ($nmkeg[$bar['kegiatan']]==''?$namakegiatan:$nmkeg[$bar['kegiatan']]) . "</td>";
			$tab.="<td align=center>" . $bar['satuan'] . "</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['hk'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total']/$bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total'])."</td>";

			$ttl+=$bar['total'];

			if($kodecapex==''){
				if($namakegiatan==''){
					$tab.="<td align=center width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_edit.png onclick=\"editdetail2('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."','".$bar['satuan']."','".$bar['volume']."','".$bar['total']."','".$bar['hk']."');\" title='Edit'></td>";
					$tab.="<td align=center width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."');\" title='Delete'>";
					$tab.="</td>";
				}else{
					$tab.="<td align=center colspan=2 width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_edit.png onclick=\"editdetail2('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."','".$bar['satuan']."','".$bar['volume']."','".$bar['total']."','".$bar['hk']."');\" title='Edit'>";
					$tab.="</td>";
				}
			}
			$tab.="</tr>";
		}


		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=7>TOTAL</td>";
		$tab.="<td align=right>".hidezerodecimal($ttl)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="</tr>";
	}
	$tab.= "</table>";

	echo $tab;
	break;

case 'showupload':

		$arrmodul = getmodulefil($emodul);
		foreach($arrmodul as $key=>$val){
			if ($key==$jnsupspkfinal) {
				$optf="<option value='".$key."'>".$val['kriteria']."</option>";
			}else{
				$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
			}
		}

		if ($jenisupload=='1') {
			$optkriteria=$optf;
		}

		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notransaksi."','".$jenisupload."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;

case 'submitfile':
	if($notransaksi==''){
		exit("Warning : Silahkan isikan detail transaksi terlebih dahulu.");
	}
	#cek data
	$sql = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
	$res=fetchData($sql);
	if(count($res)==0){
		exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu.');
	}
	$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."'";

	if ($jenisupload=='1') {
		$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and id='".$jnsupspkfinal."'";
	}

	$res=fetchData($str);
	if(count($res)>=10){
		exit("Warning : Limit upload hanya 10 file.");
	}
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			//$filename = $pt."_".$tgl."".$filetype;
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				/*if($_FILES['file']['size'] <= 250000){*/
					$str = "insert into ".$dbname.".listfile_lgl_pengajuanspk values ('','".$notransaksi."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				/*}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}*/
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	break;
case 'loadfiles':
	$no = 0;
	$tab = "";
	$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and status='1' and termin=''";
	if ($jenisupload=='1') {
		$str="select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi = '".$notransaksi."' and status='1'";
	}
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
			$icon=seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
				</td>";
			$nfile='';
			// if(strlen($val['namafile'])>10){
				// $nfile = potongtext($val['namafile'],10).$val['formaticon'];
			// }else{
				$nfile = $val['namafile'];
			// }
			$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
			<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=zImgBtn	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."','".$jenisupload."');\" >";
			$tab."	</td>
				</tr>";
		}
	}
	echo $tab;
	break;
	case'viewfile':
	$tab="";
	$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
	echo $tab;
	break;
case 'deletefile':
	$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;

case'carinoperbandingan':
	$nmpro=makeOption($dbname,'project','kode,nama');
	$optSupplierCr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select b.supplierid,b.notransaksi,a.keterangan from ".$dbname.".lgl_penawaranharga a left join ".$dbname.".lgl_penawaranhargadt b on a.notransaksi=b.notransaksi and a.nourut=b.nourut where tanggal='".$tanggalsurat."' and statuspersetujuan='1' and flag='1'";
	$res=fetchData($str);
	$form="<fieldset><legend><i>".$_SESSION['lang']['result']."</i></legend>
			<div>
				<table class=sortable cellpadding=2 cellspacing=1>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['kodesupplier']."</td>
					</tr>";
					if(count($res)>0){
						foreach ($res as $v) {
							$form.="<tr class=rowcontent style='cursor:pointer' onclick=\"getrekanan('".$v['notransaksi']."','".$v['supplierid']."','".$nmpro[$v['keterangan']]."');\">
								<td align=center>".$v['notransaksi']."</td>
								<td align=center>".getNamaSupplier($v['supplierid'])."</td>
							</tr>";
						}
					}else{
						$form.="<tr class=rowcontent><td colspan=2 align=center><i>".$_SESSION['lang']['errdatanotexist']." perbandingan harga project pada tanggal </i> <b>".tglnmbln($tanggalsurat,'I','long')."</b></td></tr>";
					}
				$form.="
				</table>
			</div>
			</fieldset>";
	echo $form;
    break;
}
function encrypt( $q, $key='') {
	if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
}
function decrypt( $q, $key='') {
    if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}

# Fungsi di pakai pada saat menyimpan hasil dari <textarea></textarea>
# $a => value yang akan di rubah
# $x => akan di replace menggunakan ??, default = ####
/* function replaceEnter($a, $x="####"){
	$a = nl2br($a);
	$i = explode('<br />',$a);
	$no =''; $t='';
	foreach($i as $r => $e){
		$no+=1;
		if($no < count($i)){
			$t.=trim($e).$x;
		}else{
			$t.=trim($e);
		}
	}
	return $t;
} */
?>