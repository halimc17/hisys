<?

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


function generatenotransaksitbsbeli (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(CONVERT(SUBSTRING_INDEX(notransaksi, '/', 1),UNSIGNED INTEGER)) as nomor 
	from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	$noba=addZero($nourut,4)."/".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}


function generatenobatransportir() {
    global $dbname;
    global $owlPDO;
    // global $nokontrak;
    global $unit;
    global $tipe;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	// exit("Error:".$unit._.$tipe._.$tanggal._.$table);
	
	
	$str="select * from ".$dbname.".".$table." where unit = '".$unit."' and tipe='".$tipe."' and tanggal like '".$tahun."%'  order by substr(notransaksi,1,3) desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$explnotran=explode('/',$bar['notransaksi']);
	@$nourut=$explnotran[0]+1;

	$notransaksi=addZero($nourut,3)."/BA-TRANSPORT/".strtoupper($tipe)."/".$unit."/".romawi($bulan)."/".$tahun;
	return $notransaksi;
}

// <br /><b>Notice</b>:  Undefined variable: explnotran in <b>C:\programing\xampp\htdocs\owl\ithaca\pmn_spk_nospk_slave.php</b> on line <b>31</b><br />001/ETC/BPJM/VIII/2021
function generatenospk() {
    global $dbname;
    global $owlPDO;
    // global $nokontrak;
    global $jenis;
    global $kodept;
    global $kodebarang;
    global $arrinisial;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	// exit("Error:".$nokontrak._.$jenis._.$kodept._.$kodebarang._.$arrinisial[$kodebarang]._.$tanggal._.$tahun._.$bulan);
	
	// $nospk="/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// $nospk="/".$jenis."-".$kodept;

	
	$str="select * from ".$dbname.".".$table." where jenis = '".$jenis."' and tanggal like '".$tahun."%'  order by substr(nospk,1,3) desc limit 1";
	// exit("Error:$str");
	// $str="select count(*) as nomor from ".$dbname.".".$table." where nospk like '%".$nospk."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	// $nourut=$bar['nomor']+1;
		$explnotran=explode('/',$bar['nospk']);
	$nourut=$explnotran[0]+1;
	// exit("Error:$nourut");

	$nospk=addZero($nourut,3)."/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// exit("Error:".$nospk._.$str);
	return $nospk;
}


function generatenobl() {
    global $dbname;
    global $owlPDO;
    // global $nokontrak;
    global $jenis;
    global $kodept;
    global $kodebarang;
    global $arrinisial;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	// exit("Error:".$nokontrak._.$jenis._.$kodept._.$kodebarang._.$arrinisial[$kodebarang]._.$tanggal._.$tahun._.$bulan);
	
	// $nospk="/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// $nospk="/".$jenis."-".$kodept;

	
	$str="select * from ".$dbname.".".$table." where jenis = '".$jenis."' and tanggal like '".$tahun."%' order by nobl desc limit 1";
	// exit("Error:$str");
	// $str="select count(*) as nomor from ".$dbname.".".$table." where nospk like '%".$nospk."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	// $nourut=$bar['nomor']+1;
		$explnotran=explode('/',$bar['nobl']);
	$nourut=$explnotran[0]+1;
	// exit("Error:$nourut");

	$nospk=addZero($nourut,3)."/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// exit("Error:".$nospk._.$str);
	return $nospk;
}

function generatenobast() {
    global $dbname;
    global $owlPDO;
    // global $nokontrak;
    global $jenis;
    global $kodept;
    global $kodebarang;
    global $arrinisial;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	// exit("Error:".$nokontrak._.$jenis._.$kodept._.$kodebarang._.$arrinisial[$kodebarang]._.$tanggal._.$tahun._.$bulan);
	
	// $nospk="/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// $nospk="/".$jenis."-".$kodept;

	
	$str="select * from ".$dbname.".".$table." where tanggal like '".$tahun."%' order by notransaksi  desc limit 1";
	// exit("Error:$str");
	// $str="select count(*) as nomor from ".$dbname.".".$table." where nospk like '%".$nospk."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	// $nourut=$bar['nomor']+1;
		$explnotran=explode('/',$bar['notransaksi']);
	$nourut=$explnotran[0]+1;
	// exit("Error:$nourut");

	$nospk=addZero($nourut,3)."/".$jenis."-".$kodept."/".$arrinisial[$kodebarang]."/".romawi($bulan)."/".$tahun;
	// exit("Error:".$nospk._.$str);
	return $nospk;
}

function generatenobapengiriman (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $kodetangki;
    global $arrinisial;
    global $kodebarang;
    global $tanggal;
    global $table;
	
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$explnotran=explode('/',$bar['nomor']);
	$nourut=$explnotran[0]+1;

	$noba=addZero($nourut,3)."/BA-".$arrinisial[$kodebarang]."/".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}

function generatenotransaksitbskud (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
	global $divisi;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(CONVERT(SUBSTRING_INDEX(notransaksi, '/', 1),UNSIGNED INTEGER)) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;
		}
		
	$noba=addZero($nourut,4)."/TBSKUD/".$unit."-".$divisi."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}


function generatenotransaksitbsexternal (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	$noba=addZero($nourut,4)."/TBSEXT/".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}


function generatenotransaksitbsafiliasi (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
    global $divisi;
	
	$table='kebun_tbsafiliasi';
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."'  and tanggal like '".$tahun."%' ";
	// exit("Error:$str");
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	// $noba=addZero($nourut,4)."/TBSAFI/".$unit."/".romawi($bulan)."/".$tahun;
	$noba=addZero($nourut,4)."/TBSAFI/".$unit."-".$divisi."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}


function generatenotransaksitbsinternal (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
    global $divisi;
	
	$table='kebun_tbsinternal';
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."'  and tanggal like '".$tahun."%' ";
	// exit("Error:$str");
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	// $noba=addZero($nourut,4)."/TBSAFI/".$unit."/".romawi($bulan)."/".$tahun;
	$noba=addZero($nourut,4)."/TBSINT/".$unit."-".$divisi."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}

function generatebakoreksistok (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $kodetangki;
    global $arrinisial;
    global $kodebarang;
    global $tanggal;
    global $table;
	
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select count(*) as nomor  from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$nourut=$bar['nomor']+1;
		// $explnotran=explode('/',$bar['nomor']);
	// $nourut=$explnotran[0]+1;

	$noba=addZero($nourut,3)."/BA-KOREKSI-".$arrinisial[$kodebarang]."/".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}


function generatenobamutasi (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $kodetangki;
    global $arrinisial;
    global $kodebarang;
    global $tanggal;
    global $table;
    global $tipe;
	
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$explnotran=explode('/',$bar['nomor']);
	$nourut=$explnotran[0]+1;

	$noba=addZero($nourut,3)."/BA-MUTASI-".$tipe."-".$arrinisial[$kodebarang]."/".$unit."/".romawi($bulan)."/".$tahun;
	
	return $noba;
}


function generatenobatransferproduk (){
	
    global $dbname;
    global $owlPDO;
    global $tipe;
    global $unit;
    global $kodept;
    global $kodetangki;
    global $arrinisial;
    global $tanggalmulai;
    global $table;
    global $tanggal;
	
	
	$str=" select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$komoditi=$bar['komoditi'];
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	// exit("Error:$tanggal");
	
	// $str="select count(*) as nomor from ".$dbname.".".$table." where tipe = '".$tipe."'";
	$str="select count(*) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' and tipe='".$tipe."'";

	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
	$nourut=$bar['nomor']+1;
		// $explnotran=explode('/',$bar['nomor']);
	// $nourut=$explnotran[0]+1;

	// $noba=addZero($nourut,3)."/".$tipe."-".$unit."/".$komoditi."/".romawi($bulan)."/".$tahun;
	$noba=addZero($nourut,3)."/BA-TRANSFER-".$tipe."-".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:xxxxxxx".$noba._.$str);
	return $noba;
}




function generatefeetbs (){
	
    global $dbname;
    global $owlPDO;
    global $unit;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select max(notransaksi) as nomor from ".$dbname.".".$table." where unit='".$unit."' and tanggal like '".$tahun."%' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		if($bar['nomor']==''){
			$nourut=1;
		}else{
			$explnotran=explode('/',$bar['nomor']);
			$nourut=$explnotran[0]+1;

		}
		
	$noba=addZero($nourut,4)."/FEETBS/".$unit."/".romawi($bulan)."/".$tahun;
	// exit("Error:$noba");
	return $noba;
}




function generatepengajuancucitangki() {

    global $dbname;
    global $owlPDO;
    global $jenis;
    global $kodeunit;
    global $kodeorg;
    global $kodebarang;
    global $tanggal;
    global $table;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select * from ".$dbname.".".$table." where kodeorg='".$kodeorg."' and tanggal like '".$tahun."%' order by notransaksi desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$explnotran=explode('/',$bar['notransaksi']);
	$nourut=$explnotran[0]+1;
	$nospk=addZero($nourut,3)."/CUCITANGKI/".$kodeorg."/".romawi($bulan)."/".$tahun;
	
	return $nospk;
}






function generatebacucitangki() {

    global $dbname;
    global $owlPDO;
    global $jenis;
    global $kodebarang;
    global $tanggal;
    global $table;
    global $kodeorg;
	
	$tahun=explode('-',$tanggal);
	$bulan=$tahun[1];
	$tahun=$tahun[0];
	
	$str="select * from ".$dbname.".".$table." where kodeorg='".$kodeorg."' and tanggal like '".$tahun."%' order by notransaksi desc limit 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();
		$explnotran=explode('/',$bar['notransaksi']);
	$nourut=$explnotran[0]+1;
	$nospk=addZero($nourut,3)."/BACUCITANGKI/".$kodeorg."/".romawi($bulan)."/".$tahun;
	
	return $nospk;
}





?>