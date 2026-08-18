<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');


$param = $_POST;

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");

if($tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'HOLDING' and $tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'KANWIL') {
    exit('<label hidden>Warning</label> Untuk Proses Depresiasi Asset, hanya lokasi tugas Holding (HO) dan Kanwil (RO) yang bisa lakukan Proses');
}

/*
kodeorg=trim(document.getElementById('kodeorg').value);
	periode=trim(document.getElementById('periode').value);
	jenisData=trim(document.getElementById('jenisData').value);
	
	
    tipeasset=trim(document.getElementById('tipeasset'+currRow).innerHTML);
    keterangan=trim(document.getElementById('keterangan'+currRow).innerHTML);
    kodeasset=trim(document.getElementById('kodeasset'+currRow).innerHTML);
    namaaset=trim(document.getElementById('namaaset'+currRow).innerHTML);
    kodejurnal=trim(document.getElementById('kodejurnal'+currRow).innerHTML);
    jumlah=trim(document.getElementById('jumlah'+currRow).innerHTML);
    debet=trim(document.getElementById('debet'+currRow).innerHTML);
    kredit=trim(document.getElementById('kredit'+currRow).innerHTML);
*/

// exit("Error".$param['tanggal']._.$periodejurnal);
#= bentuk data kodept	

$str="select * from ".$dbname.".organisasi where  kodeorganisasi = '".$param['kodeorg']."'";

$res=fetchdata($str);
$tipeorg = $res[0]['tipe'];

$tanggal=$param['periode']."-28";


try {

	$owlPDO->beginTransaction();
	
	#= delete 1st jika row 1
	if($param['currRow']==1){
		$str="select * from ".$dbname.".sdm_5tipeasset";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kdjurnal['DPH'.$bar['kodetipe']]='DPH'.$bar['kodetipe'];
			$kdjurnal['DEP'.$bar['kodetipe']]='DEP'.$bar['kodetipe'];
		}
		$str="delete from ".$dbname.".keu_jurnalht where  nojurnal like '%/".$param['kodeorg']."/%' and kodejurnal in ('".implode("','",$kdjurnal)."') and  tanggal='".$tanggal."' ";	
		$owlPDO->exec($str);
	}
	
	if($param['kodejurnal']==''){
		exit("error: Gagal melakukan jurnal untuk asset ".$param['kodeasset'].", Kode Jurnal masih kosong");
	}

	$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
	"kodekelompok='".$param['kodejurnal']."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."'");
	$tmpKonter = fetchData($query);
	$konter = addZero($tmpKonter[0]['nokounter']+1,3);
	# Prep No Jurnal
	$nojurnal = str_replace('-','',$tanggal)."/".$param['kodeorg']."/".$param['kodejurnal']."/".$konter;
	// $nojurnal = str_replace('-','',$tanggal)."/".$param['kodeorg']."/".$param['kodejurnal']."/".$param['currRow'];

	$param['jumlah']=str_replace(',','',$param['jumlah']);
	

	$dataRes['header'][] = array(
		'nojurnal'=>$nojurnal,
		'kodejurnal'=>$param['kodejurnal'],
		'tanggal'=>$tanggal,
		'tanggalentry'=>date('Ymd'),
		'posting'=>'0',
		'totaldebet'=>$param['jumlah'],
		'totalkredit'=>$param['jumlah']*-1,
		'amountkoreksi'=>'0',
		'noreferensi'=>$param['kodeasset'],
		'autojurnal'=>'1',
		'matauang'=>'IDR',
		'kurs'=>'1',
		'revisi'=>'',
		// 'bagian'=>'0', //	varchar(20)	departemen
		// 'others'=>'0', //varchar(20)	inputan bebas tergantung kebutuhan masing - masing
		// 'kuantitas'=>'0', //	double	untuk kuantitas contoh gudang kolom ini untuk qty barang
		// 'createby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill	
		// 'createtime'=>date("Y-m-d H:i:s"), //	datetime	
		// 'updateby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill
		// 'updatetime'=>date("Y-m-d H:i:s") //	datetime
	);
	
	#= update counter jurnal
	$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where 
		kodeunit='".$param['kodeorg']."' and kodekelompok='".$param['kodejurnal']."' and periode='".$param['periode']."' ";	
	$owlPDO->exec($str);
	// print_r($dataRes);
				
	$noUrut=0;

	#= debet
	$noUrut++;
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tanggal,
		'nourut'=>$noUrut,
		'noakun'=>$param['debet'],
		'keterangan'=>'Penyusutan ['.$param['kodeasset'].'] '.$param['namaaset'],
		'jumlah'=>$param['jumlah'],
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$param['kodeorg'],
		'kodekegiatan'=>'',
		'kodeasset'=>$param['kodeasset'],
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>$param['kodeasset'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment' =>''
		// 'bagian'=>'0', //	varchar(20)	departemen
		// 'others'=>'0', //varchar(20)	inputan bebas tergantung kebutuhan masing - masing
		// 'kuantitas'=>'0', //	double	untuk kuantitas contoh gudang kolom ini untuk qty barang
		// 'createby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill	
		// 'createtime'=>date("Y-m-d H:i:s"), //	datetime	
		// 'updateby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill
		// 'updatetime'=>date("Y-m-d H:i:s") //	datetime
	);
	
	#= kredit
	$noUrut++;
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tanggal,
		'nourut'=>$noUrut,
		'noakun'=>$param['kredit'],
		'keterangan'=>'Penyusutan ['.$param['kodeasset'].'] '.$param['namaaset'],
		'jumlah'=>$param['jumlah']*-1,
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$param['kodeorg'],
		'kodekegiatan'=>'',
		'kodeasset'=>$param['kodeasset'],
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>'',
		'noreferensi'=>$param['kodeasset'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		'nodok'=>'',
		'kodeblok'=>'',
		'revisi'=>'0',
		'kodesegment' =>''
		// 'bagian'=>'0', //	varchar(20)	departemen
		// 'others'=>'0', //varchar(20)	inputan bebas tergantung kebutuhan masing - masing
		// 'kuantitas'=>'0', //	double	untuk kuantitas contoh gudang kolom ini untuk qty barang
		// 'createby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill	
		// 'createtime'=>date("Y-m-d H:i:s"), //	datetime	
		// 'updateby'=>$_SESSION['standard']['userid'], //	int(10) unsigned zerofill
		// 'updatetime'=>date("Y-m-d H:i:s") //	datetime
	);

	$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
	// exit("Error:$queryH");
	$owlPDO->exec($queryH);

	$queryD = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
	$owlPDO->exec($queryD);
	
	$owlPDO->commit();
	
} catch(PDOException $e) {
	
	$owlPDO->rollback();
	echo "Warning Gagal jurnal \n" . addslashes($e->getMessage());

}

?>