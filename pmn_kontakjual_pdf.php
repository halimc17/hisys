<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
//require_once('lib/zFunction.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/zLib.php');
require_once('lib/terbilang.php');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
//=============
$noKontrak=$_GET['column'];
$nmPt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$defaultsize=12;
$defaultsizekop=25;
$defaultsizekoppanjang=20;
$high=6;
$highkop=15;

$str="select * from ".$dbname.".".$_GET['table']."  where nokontrak='".$noKontrak."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$dtcatatanlain=$bar['catatanlain'];
	$dtperselisihan=$bar['perselisihan'];

if($dtcatatanlain!='' and $dtperselisihan!=''){
	$defaultsize=9;
	$high=4.6;
}

$sizekiri=45;

//create Header
if (!class_exists('PDFKJ')){
	class PDFKJ extends FPDF{
		function Header(){
			global $conn;
			global $dbname;
			global $userid;
			global $posting;
			global $noKontrak;
			global $kodePt;
			global $kdBrg;
			global $tlgKontrk;
			global $kdCust;
			global $nmBrg;
			global $wilKota;
			global $nama;
			global $bar;
			global $arrStatPPn;
			global $owlPDO;
			global $initialBrg;
			global $defaultsize;
			global $high;
			global $defaultsizekop;
			global $highkop;
			global $franco;
			global $nokontrakDis;
			global $tipepenjualan;
			global $defaultsizekoppanjang;

			$noKontrak=$_GET['column'];
			
			$str="select * from ".$dbname.".".$_GET['table']."  where nokontrak='".$noKontrak."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$kodePt=$bar['kodept'];
			$kdBrg=$bar['kodebarang'];
			$tlgKontrk=tanggalnormal($bar['tanggalkontrak']);
			$kdCust=$bar['koderekanan'];
			$tipepenjualan=$bar['tipepenjualan'];
			$nokontrakDis=$noKontrak;
			// if($bar['nokontrakexternal']!=''){
				// $nokontrakDis=$bar['nokontrakexternal'];
			// }
			
			
			
			if($kdBrg=='40000003'){
				$nokontrakDis=$bar['nokontrakexternal'];
			}

			$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodePt."'"; 
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch()){ 
				$nama=$bar1->namaorganisasi;
				$alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				$telp=$bar1->telepon;	
				$wilKota=$bar1->wilayahkota;			 
			}
			
			$sBrg="select * from ".$dbname.".log_5masterbarang where kodebarang='".$kdBrg."'";
			$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
			$qBrg->setFetchMode(PDO::FETCH_ASSOC);
			$rBrg=$qBrg->fetch();
			$nmBrg=$rBrg['namabarang'];
			$initialBrg=$rBrg['inisial'];

			$arrHead = setheadreport($kodePt);
			$path=$arrHead['logo'];
			$widthlogo=$arrHead['logowidth'];
			$heightlogo=$arrHead['logoheight'];
			
		
			if(strlen($arrHead['nama'])>25){
				$defaultsizekopx=$defaultsizekoppanjang;
			}else{
				$defaultsizekopx=$defaultsizekop;
			}
			
			$this->Image($path,20,5,0,30);
			$this->SetFont('Arial','B',$defaultsizekopx);
			$this->SetFillColor(255,255,255);	

			$this->SetXY(55,10);
			$this->Cell(50,$highkop,$arrHead['nama'],0,1,'L');	 
			$this->Ln(15);
		}
		
		function Footer(){

			global $conn;
			global $dbname;
			global $owlPDO;
			global $kodePt;

			$str1="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodePt."'"; 
			$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while($bar1=$res1->fetch()){ 
				$nama=$bar1->namaorganisasi;
				// $alamatpt=$bar1->alamat.", ".$bar1->wilayahkota;
				$alamatpt=$bar1->alamat;
				$telp=$bar1->telepon;	
				$fax=$bar1->fax;	
				$wilKota=$bar1->wilayahkota;			 
			}

			$this->SetY(-20);
			$this->SetFont('Arial','B',10);
			// $this->Cell(170,5,$nama,0,1,'C');
			$this->SetFont('Arial','',10);
			$this->Cell(170,5,'Alamat Korespondensi :',0,1,'C');
			$this->Cell(170,5,$alamatpt,0,1,'C');
			$this->Cell(170,5,"Telp. ".$telp." Fax. ".$fax,0,0,'C');
		}
	}
}

$pdf=new PDFKJ('P','mm','legal');
$pdf->SetMargins(20,'',20);
$pdf->AddPage();





$optKd=makeOption($dbname,'pmn_4komoditi','kodebarang,kodekomoditi',"kodebarang = '".$kdBrg."'");

if ($kdBrg==40000002)
{
	$prdk='Inti Kelapa Sawit (Palm Kernel)';
}
else if ($kdBrg==40000001)
{
	$prdk='Minyak Kelapa Sawit (Crude Palm Oil)';
}

$pdf->SetFont('Arial','',$defaultsize);
$pdf->Cell(180,$high,strtoupper('perjanjian jual beli'),0,1,'C');
$pdf->Cell(180,$high,strtoupper('Produk '.$prdk),0,1,'C');
$pdf->SetFont('Arial','B',$defaultsize);
$pdf->Cell(180,$high,"No : ".$nokontrakDis,0,1,'C');
$pdf->Ln($high);				

$arrStatPPn=array(0=>"Exclude",1=>"Include");

$pdf->SetFont('Arial','',$defaultsize);


#data pembeli 
$whrpemb="kodecustomer='".$kdCust."'";
$optNm=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',$whrpemb);
$optNmAlmt=makeOption($dbname,'pmn_4customer','kodecustomer,alamatnpwp',$whrpemb);
$optNpwp=makeOption($dbname,'pmn_4customer','kodecustomer,npwp',$whrpemb);
$optBrk=makeOption($dbname,'pmn_4customer','kodecustomer,statusberikat',$whrpemb);
$optKtrgBrk=makeOption($dbname,'pmn_4customer','kodecustomer,keteranganberikat',$whrpemb);

//Syarat Penyerahan
$iFranco=" select * from ".$dbname.".pmn_5franco where id_franco='".$bar['franco']."' ";
$nFranco=$owlPDO->query($iFranco) or die(print " Gagal: ".PDOException::getMessage());
$nFranco->setFetchMode(PDO::FETCH_ASSOC);
$dFranco=$nFranco->fetch();

$pdf->Cell($sizekiri,$high,$_SESSION['lang']['Pembeli'],'',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->SetFont('Arial','',$defaultsize);
$nmdt2=explode(".",$optNm[$kdCust]);
if(count($nmdt2)==0){
	$nmdt2=$optNm[$kdCust];
}
$pdf->Cell(100,$high,@$nmdt2[0].".".ucwords(strtolower(@$nmdt2[1])),'',1,'L');
$pdf->Cell($sizekiri,$high,'','',0,'L');
$pdf->Cell(5,$high,'','',0,'L');
// $pdf->Cell(100,$high,$optNmAlmt[$kdCust],'',1,'L');

$string = preg_replace_callback('/\b(?=[LXIVCDM]+\b)([a-z]+)\b/i', 
function($matches) {
    return strtoupper($matches[0]);
}, ucwords(strtolower($optNmAlmt[$kdCust])));

$pdf->MultiCell(140,5,$string,0,'L',0);

$pdf->Cell(5,$high,'','',1,'L');

$whrKomo="kodecustomer='".$kdCust."' and kodebarang='".$kdBrg."'";
$optKomo=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');

$pdf->Cell($sizekiri,$high,'Nama Barang','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->Cell(100,$high,$prdk,'',1,'L');


//7 Satuan
$whrmt="kode='".$bar['matauang']."'";
$optMtSim=makeOption($dbname,'setup_matauang','kode,simbol',$whrmt);
$optMtuang=makeOption($dbname,'setup_matauang','kode,matauang',$whrmt);

if($bar['ppn']==0){
	$isiketeranganppn="+ PPN  ".$bar['defaultpersenppn']."%";
}

$isiharga=$tipepenjualan.' '.$dFranco['franco_name'].' '.$optMtSim[$bar['matauang']]." ".number_format($bar['hargasatuan'],0)." Per ".ucwords(strtolower($bar['satuan']))." ".$isiketeranganppn;


$pdf->Cell($sizekiri,$high,$_SESSION['lang']['hargasatuan'],'',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->Cell(100,$high,$isiharga,'',1,'L');


//Kuantitas
$pdf->SetFont('Arial','',$defaultsize);
$pdf->Cell($sizekiri,$high,$_SESSION['lang']['jumlah'],'',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->Cell(100,$high,number_format($bar['kuantitaskontrak'])." ".ucwords(strtolower($bar['satuan'])),'',1,'L');


$pdf->Cell(5,$high,'','',1,'L');

//Jumlah harga
$nilKontrak=$bar['hargasatuan']*$bar['kuantitaskontrak'];
$pdf->Cell($sizekiri,$high,'Nilai Penjualan','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->Cell(100,$high,$optMtSim[$bar['matauang']]." ".number_format($nilKontrak,0)." ".$isiketeranganppn,'',1,'L');

//Terbilang
$pdf->Cell($sizekiri,$high,'','',0,'L');
$pdf->Cell(5,$high,'','',0,'L');
$nilKontrak=number_format($nilKontrak);
$nilKontrak=str_replace(',','',$nilKontrak);
$pdf->MultiCell(130,$high,"(".$optMtuang[$bar['matauang']]." ".ucwords(terbilang($nilKontrak,2))." ".$isiketeranganppn.")",0,'L',0);

$pdf->Cell($sizekiri,$high,'','',1,'L');


$pdf->Cell($sizekiri,$high,'Tata Cara Pembayaran','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->MultiCell(130,$high,$bar['ketbayarpelunasan'],0,'L',0);
//$pdf->MultiCell(130,$high,$bar['ketbayarpelunasan'],'',1,'L');

$pdf->Cell($sizekiri,$high,'','',1,'L');

// $valSyaratPenyerahan = $dFranco['franco_name']." - ".$dFranco['alamat'];
$valSyaratPenyerahan = $dFranco['franco_name'];
$pdf->Cell($sizekiri,$high,'Lokasi Pemuatan','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->MultiCell(130,$high,$valSyaratPenyerahan,0,'L',0);

$pdf->Cell($sizekiri,$high,'Jadwal Penyerahan','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');


if($bar['sdtanggal']!='0000-00-00'){
	$tglkirim=tglnmbln($bar['tanggalkirim'],'I','long').' - '.tglnmbln($bar['sdtanggal'],'I','long');
}else{
	$tglkirim=tglnmbln($bar['tanggalkirim'],'I','long');
}

$pdf->Cell(5,$high,$tglkirim,'',1,'L');
// echo $bar['tanggalkirim1'];
//Kualitas
$ffaData=number_format($bar['ffa'],2).' ';
$dobiData=number_format($bar['dobi'],2).' ';
$mdaniData=number_format($bar['mdani'],2).' ';
$moistData=number_format($bar['moist'],2).' ';
$dirtData=number_format($bar['dirt'],2).' ';
$gradingData=number_format($bar['grading'],2).' ';


if($ffaData==0 and $dobiData==0 and $mdaniData==0 and $moistData==0 and $dirtData==0 and $gradingData==0){
	// $pdf->Cell($sizekiri,$high,$_SESSION['lang']['kualitas'],'',0,'L');
    // $pdf->Cell(5,$high,':','',1,'L');
}else{
	if($kdBrg!='40000005'){
		$pdf->Cell($sizekiri,$high,'Standar Mutu','',0,'L');
    	$pdf->Cell(5,$high,':','',0,'L');	
	}
}




	if($kdBrg=='40000001'){
		$namaffa="FFA";
		$namadobi="Dobi";
		$namamni="M & I";
		$namamois="Moisture";
		$namaimpu="Impurities";
		$namadirt="Dirt";
		
	}else{
		$namaffa="FFA";
		$namadobi="Dobi";
		$namamni="M & I";
		$namamois="Kadar Air";
		$namaimpu="Impurities";
		$namadirt="Kadar Kotoran";
		
	}
	
	

$lengthkual=30;
$setxmutu=70;

if($ffaData!=0){
	$pdf->Cell($lengthkual,$high,$namaffa,'',0,'L');
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$ffaData.' % Max','',1,'L');
}

if($dobiData!=0){
	$pdf->SetX($setxmutu);
    $pdf->Cell($lengthkual,$high,$namadobi,'',0,'L');
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$dobiData.' Min','',1,'L');
}

if($mdaniData!=0){
	$pdf->SetX($setxmutu);
    $pdf->Cell($lengthkual,$high,$namamni,'',0,'L');
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$mdaniData.' % Max','',1,'L');
}

if($moistData!=0){
	$pdf->SetX($setxmutu);
    $pdf->Cell($lengthkual,5,$namamois,'',0,'L');
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$moistData.' % Max','',1,'L');
}

if($dirtData!=0){
	$pdf->SetX($setxmutu);
    $pdf->Cell($lengthkual,$high,$namaimpu,'',0,'L');
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$dirtData.' % Max','',1,'L');
}

if($gradingData!=0){
	$pdf->SetX($setxmutu);
	if($kdBrg=='40000003'){
		$pdf->Cell($lengthkual,$high,'Grading','',0,'L');
	}else{
		$pdf->Cell($lengthkual,$high,$namadirt,'',0,'L');
	}
    $pdf->Cell(5,$high,':','',0,'L');
    $pdf->Cell(5,$high,$gradingData.' % Max','',1,'L');
}


if($bar['sdtanggal3']=='0000-00-00'){
	if($bar['sdtanggal2']=='0000-00-00'){
		if($bar['sdtanggal1']=='0000-00-00'){
			$tglAkhir = $bar['sdtanggal'];
		}else{
			$tglAkhir = $bar['sdtanggal1'];
		}
	}else{
		$tglAkhir = $bar['sdtanggal2'];
	}
}else{
	$tglAkhir = $bar['sdtanggal3'];
}
if($bar['tanggalkirim']=='0000-00-00')
{
	$kettgl = "Segera";
}
else
{
	$kettgl= tglnmbln($bar['tanggalkirim'],'I','long')." - ".tglnmbln($tglAkhir,'I','long');
}
//$pdf->MultiCell(130,$high,$kettgl,0,'L',0);

$pdf->Ln();

if($bar['catatanlain']!=''){
	$pdf->Cell($sizekiri,$high,$_SESSION['lang']['catatan'],'',0,'L');
	$pdf->Cell(5,$high,':','',0,'L');
	$pdf->MultiCell(130,$high,$bar['catatanlain'],0,'L',0);	
}


//Pembayaran
$sTrmn="select distinct * from ".$dbname.".pmn_5terminbayar where kode='".$bar['kdtermin']."'";
$qTrmn=$owlPDO->query($sTrmn) or die(print " Gagal: ".PDOException::getMessage());
$qTrmn->setFetchMode(PDO::FETCH_ASSOC);
$rTrmn=$qTrmn->fetch();

$sTrmn2="select * from ".$dbname.".keu_5akunbank where noakun='".$bar['rekening']."'";
$qTrmn2=$owlPDO->query($sTrmn2) or die(print " Gagal: ".PDOException::getMessage());
$qTrmn2->setFetchMode(PDO::FETCH_ASSOC);
$rTrmn2=$qTrmn2->fetch();

$bulan=substr($bar['tglpembayarpertama'],5,2);
$nmBulan=numToMonth($bulan,'I','long');
$thn=substr($bar['tglpembayarpertama'],0,4);
$tglnya=substr($bar['tglpembayarpertama'],8,2);
$listTgl=$tglnya.' '.$nmBulan.' '.$thn;

$optNamaBank = makeOption($dbname,"keu_5daftarbank",'kodebank,namabank',"kodebank='".$rTrmn2['namabank']."'");

$ktTermin='';

$pdf->Ln(5);
$pdf->Cell(30,$high,'Transfer Pembayaran ke :','',1,'L');
$pdf->SetFont('Arial','B',$defaultsize);
$pdf->Cell(30,$high,$rTrmn2['atasnama'],'',1,'L');
$pdf->SetFont('Arial','',$defaultsize);
$pdf->Cell(5,$high,$optNamaBank[$rTrmn2['namabank']].', '.$rTrmn2['cabang'],'',1,'L');
$pdf->SetFont('Arial','B',$defaultsize);
$pdf->Cell(15,$high,'A/C No','',0,'L');
$pdf->Cell(5,$high,''.$rTrmn2['rekening'],'',1,'L');
$pdf->SetFont('Arial','',$defaultsize);
$pdf->Ln(5); 
//$pdf->MultiCell(130,5,$ktTermin,0,'L',0);

$pdf->Cell($sizekiri,$high,'Catatan','',0,'L');
$pdf->Cell(5,$high,':','',0,'L');
$pdf->MultiCell(130,$high,'Kontrak ini merupakan bagian yang tidak dapat dipisahkan dengan  perjanjian jual beli format panjang dengan nomor yang sama.',0,'L',0);


if($bar['forcemajuere']!=''){
	$pdf->Cell($sizekiri,$high,'Force Majuere','',0,'L');
	$pdf->Cell(5,$high,':','',0,'L');
	$pdf->MultiCell(130,$high,$bar['forcemajuere'],0,'L',0);	
}
if($bar['perselisihan']!=''){
	$pdf->Cell($sizekiri,$high,'Perselisihan','',0,'L');
	$pdf->Cell(5,$high,':','',0,'L');
	$pdf->MultiCell(130,$high,$bar['perselisihan'],0,'L',0);	
}

//Catatan

$pdf->Ln(5);

//BEGIN Tanda Tangan        
$tglTtd=explode("-",$tlgKontrk);
$tglnya=$tglTtd[0];
$blnnya=numToMonth($tglTtd[1],$lang='I',$format='long');
$thnnya=$tglTtd[2];   
$tglbenernya=$tglnya.' '.$blnnya.' '.$thnnya;

$strx="select * from ".$dbname.".pmn_5daerahkontrak where id='".$bar['daerahkontrak']."'";
$resx=fetchData($strx);
$drhktrk = $resx[0]['lokasi'];


$pdf->Cell(1,$high,'','',0,'L');	
$pdf->Cell($sizekiri,$high,ucwords(strtolower($drhktrk)).", ".$tglbenernya,'',0,'L');
$pdf->Ln(10);
// $pdf->Ln();
$pdf->Cell(100,$high,ucfirst($_SESSION['lang']['penjual']),'',0,'L');
$pdf->Cell(80,$high,ucfirst($_SESSION['lang']['Pembeli']).'','',1,'L');
                // echo $nmPt[$bar['kodept']];

$nmPtS=explode(".",$nmPt[$bar['kodept']]);
setIt($nmPtS[1],'');
$pdf->Cell(100,$high,strtoupper($nmPtS[0]).". ".ucwords(strtolower($nmPtS[1])),'',0,'L');
$pdf->Cell(80,$high,strtoupper(@$nmdt2[0]).". ".ucwords(strtolower(@$nmdt2[1])),'',1,'L');

$nmTtd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$jabatanTtd=makeOption($dbname,'pmn_5ttd','nama,jabatan');
$namaTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,penandatangan');
$jabTtdBeli=makeOption($dbname,'pmn_4customer','kodecustomer,jabatan');
                
				
$datattdcustomer=explode('/',$namaTtdBeli[$bar['koderekanan']]);

if($datattdcustomer[1]!=''){
	$ttdcustomer=ucwords(strtolower($datattdcustomer[0])).'/'.ucwords(strtolower($datattdcustomer[1]));
}else{
	$ttdcustomer=ucwords(strtolower($datattdcustomer[0]));
}				
$pdf->Ln(33);
$pdf->SetFont('Arial','BU',$defaultsize);

$pdf->Cell(100,$high,ucwords(strtolower($nmTtd[$bar['penandatangan']])),'',0,'L');
$pdf->Cell(80,$high,$ttdcustomer,'',1,'L');

$pdf->SetFont('Arial','',$defaultsize);
$pdf->Cell(100,$high,ucwords(strtolower($jabatanTtd[$bar['penandatangan']])),'',0,'L');
$pdf->Cell(80,$high,ucwords(strtolower($jabTtdBeli[$bar['koderekanan']])),'',1,'L'); 
//END Tanda Tangan

// $tab.="<footer>";
			// $cellpadding=1;	
			// $tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
				// $tab.="<tr>";
					// $tab.="<td align=left style='width:700px;border-bottom:0.5px solid #000000'><b>".$namapt."</b></td>"; 
				// $tab.="</tr>";
				// $tab.="<tr>";
					// $tab.="<td align=left><b>".$kotaro." Office</b> : ".$alamatro." Tel : ".$telpro." Fax : ".$faxro."</td>"; 
				// $tab.="</tr>";
			// $tab.="</table>";
		// $tab.="</footer>";	


$urlefil=checkPostGet('urlefil','0');
if($urlefil=='0'){
	$pdf->Output();
}else{
	$pdf->Output($urlefil);
}
?>
