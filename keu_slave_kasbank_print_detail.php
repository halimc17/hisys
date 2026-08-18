<?php
ini_set('display_errors',0);
ini_set("session.auto_start", 0);
// error_reporting(0);
session_start();
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
	$session_id = $_SESSION['standard']['userid'];
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses','');
$param = $_GET;
$urlefil=checkPostGet('urlefil','0');

$nmMt=  makeOption($dbname, 'setup_matauang', 'kode,matauang');
$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmket=  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
$nmbank=  makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');


$nmaruskas=makeOption($dbname,'keu_5aruskas','noaruskas,nama_aruskas');

#= bentuk data kodept	
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   $kodept[$bar['kodeorganisasi']]=$bar['induk'];
}


#= approval karyawan
$str = "select * from ".$dbname.".datakaryawan  where karyawanid in (select karyawanid from ".$dbname.".approval where jenispersetujuan='KASBANK') ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmkaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
	
}


/** Report Prep **/
$cols = array();

#=============================== Header ======================================= keu_kasbankht
$whereH = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$queryH = selectQuery($dbname,'keu_kasbankht','*',$whereH);
$resH = fetchData($queryH);


# Get Nama Pembuat
$userId = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',
    "karyawanid='".$resH[0]['userid']."'");
# Get Nama Akun Hutang
$namaakunhutang = makeOption($dbname,'keu_5akun','noakun,namaakun',
    "noakun='".$resH[0]['noakunhutang']."'");
#Get tipe Lokasi Tugas
$tipeLokasiTugas = makeOption($dbname,'organisasi','kodeorganisasi,tipe');

#=============================== Detail =======================================
# Data
$col1 = 'noakun,jumlah,noaruskas,matauang,kode,keterangan2,hutangunit1,pemilikhutang1';
$cols = array('nourut','noakun','namaakun','noaruskas','debet','kredit');
$colshtml = array('nourut','noakun','namaakun','noaruskas','debet','kredit','keterangan','hutangunit1','pemilikhutang1');
//$col1 = 'noakun,jumlah,noaruskas,matauang,kode,hutangunit1';
//$cols = array('nomor','noakun','namaakun','matauang','debet','kredit','hutangunit');
$where = "notransaksi='".$param['notransaksi'].
    "' and kodeorg='".$param['kodeorg'].
    "' and noakun2a='".$param['noakun'].
    "' and tipetransaksi='".$param['tipetransaksi']."'";
$query = selectQuery($dbname,'keu_kasbankdt',$col1,$where);
$res = fetchData($query);

# Data Empty
if(empty($res)) {
    echo 'Data Empty';
    exit;
}

# Options
$whereAkun = "noakun in (";
$whereAkun .= "'".$resH[0]['noakun']."'";
$whereAkun .= ",'".$resH[0]['noakunhutang']."'"; // tambahin kamus nama akun hutangunit 
foreach($res as $key=>$row) {
    $whereAkun .= ",'".$row['noakun']."'";
}
$whereAkun .= ")";
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',$whereAkun);
$optHutangUnit = array('0'=>'Tidak','1'=>'Ya');

# Data Show
$data = array();

#================================ Prep Data ===================================
# Total
$totalDebet = 0;$totalKredit = 0;

# Dari Header
$i=1;
$data[$i] = array(
    'nomor'=>@$i,
    'noakun'=>@$resH[0]['noakun'],
    'namaakun'=>@$optAkun[$resH[0]['noakun']],
    'noaruskas'=>@$resH[0]['noaruskas'],
    'debet'=>0,
    'kredit'=>0,
    'keterangan2'=>$resH[0]['keterangan'],
    'hutangunit1'=>'99',
    'pemilikhutang1'=>'',
);

if($param['tipetransaksi']=='M') {
    $data[$i]['debet'] = $resH[0]['jumlah'];
    $totalDebet += $resH[0]['jumlah'];

} else {
    $data[$i]['kredit'] = $resH[0]['jumlah'];
    $totalKredit += $resH[0]['jumlah'];

}

if(substr($resH[0]['noakun'],0,5)=='11102')
{
        if($resH[0]['tipetransaksi']=='K'){
                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['keluar'].")");
        }else{
                $title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['masuk'].")");
        }
}
else
{
        if($resH[0]['tipetransaksi']=='K'){
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
        }else{
                $title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
        }
}


$i++;

# Dari Detail
foreach($res as $row) {
    $data[$i] = array(
                'nomor'=>$i,
                'noakun'=>$row['noakun'],
                'namaakun'=>$optAkun[$row['noakun']],
                'noaruskas'=>$row['noaruskas'],
                'debet'=>0,
                'kredit'=>0,
				'keterangan2'=>$row['keterangan2'],
				'hutangunit1'=>$row['hutangunit1'],
				'pemilikhutang1'=>$row['pemilikhutang1'],
    );
//	'hutangunit1'=>$optHutangUnit[$row['hutangunit1']]
    if($param['tipetransaksi']=='M' and $row['jumlah']>0) {
        $data[$i]['kredit'] = $row['jumlah'];
        $totalKredit += $row['jumlah'];
    }
    else if($param['tipetransaksi']=='K' and $row['jumlah']<0){
        $data[$i]['kredit'] = $row['jumlah']*-1;
        $totalKredit += $row['jumlah']*-1;        
    }
    else if($param['tipetransaksi']=='M' and $row['jumlah']<0){
        $data[$i]['debet'] = $row['jumlah']*-1;
        $totalDebet += $row['jumlah']*-1;        
    }    
    else {
        $data[$i]['debet'] = $row['jumlah'];
        $totalDebet += $row['jumlah'];
    }
    $i++;
}

// nyusun berdasarkan debet dulu, abis itu baru kredit. by dz
if(!empty($data)) foreach($data as $c=>$key) {
    $sort_debet[] = $key['debet'];
    $sort_kredit[] = $key['kredit'];
}

// sort
if(!empty($data))array_multisort($sort_debet, SORT_DESC, $sort_kredit, SORT_ASC, $data);

$align = explode(",","R,R,L,L,R,R,L,L");
$length = explode(",","7,12,35,10,18,18,10");
$titleDetail = 'Detail';

/** Output Format **/
switch($proses) {
	case'pdfnew':
	
		$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$novoucherht=$bar['novoucher'];
			$noreferensiht=$bar['noreferensi'];
			$kodeorght=$bar['kodeorg'];

		if($novoucherht==''){ #= jika belum proses kasir
			$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		} else {
			if($noreferensiht==''){
				$str = "select * from ".$dbname.".keu_kasbankht where novoucher='".$novoucherht."'";
			}else{
				$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
			}
		}
		
		
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listnotransaksi[$bar['notransaksi']]=$bar['notransaksi'];
			$tanggalht=$bar['tanggal'];
			$noakunht=$bar['noakun'];
			$rekeninght=$bar['rekening'];
			$cgttuht=$bar['cgttu'];
			$nocekht=$bar['nocek'];
			@$jumlahht+=$bar['jumlah'];
			$tipetransaksiht=$bar['tipetransaksi'];
			$kodeorght=$bar['kodeorg'];
			$bayarkepada=$bar['bayarkepada'];
			
			$createbyht=$bar['createby'];
			$kasirht=$bar['kasir'];
		}
		
		$str = "select * from ".$dbname.".keu_5akunbank where noakun='".$rekeninght."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$norekeningsetup=$bar['rekening'];
			$kodebanksetup=$bar['namabank'];
			$atasnamasetup=$bar['atasnama'];
			
		#= data dt
		
		$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and kodesupplier!=''";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();			
			$kodesupplier=$bar['kodesupplier'];
			

		#= supplier
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namasupplier=$bar['namasupplier'];		
		
		#=Tanggal Terima & Tanggal Jatuh Tempo
		$str="select * from ".$dbname.".keu_tagihanht a left join ".$dbname.".keu_kasbankdt b on a.noinvoice=b.keterangan1 
				where b.notransaksi in ('".$param['notransaksi']."') ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$tglterima[$bar['noinvoice']]=tanggalnormal($bar['tanggal']);
			@$tgljthtmpo[$bar['noinvoice']]=tanggalnormal($bar['jatuhtempo']);
		}


		$cellpadding=2;	
		
		$tab="<style>
				@page {
					margin-top: 25px;
					margin-left: 30px;
					margin-right: 30px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}
				
				footer {
					position: fixed; 
					bottom: -20px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}
				
			</style>";
		// $tab = '';
			
			
		if($tipetransaksiht=='M'){
			$namavoucher='BUKTI MASUK';
		}else{
			$namavoucher='BUKTI PENGELUARAN';
		}			
		
		$cellpadding=1;
		$fontsize='10px';
		$tab.="<table width=115% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding.">";	
			$tab.="<tr>";
				$tab.="<td colspan=4><b>".$nmorg[$kodeorght]."</b></td>"; 
				$tab.="<td colspan=2 align=left><b>".$namavoucher."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=4></td>";  
				$tab.="<td>".$_SESSION['lang']['novoucher']."</td>"; 
				$tab.="<td>: ".$novoucherht."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				if($tipetransaksiht=='M'){
					$tab.="<td style='width:30px'>Terima Dari</td>"; 
					$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
				}else{
					$tab.="<td style='width:30px'>".$_SESSION['lang']['bayarke']."</td>"; 
					$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
				}
				$tab.="<td style='width:30px'>".$_SESSION['lang']['tanggal']."</td>"; 
				$tab.="<td style='width:30px'>: ".tanggalnormal($tanggalht)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=4></td>"; 
				$tab.="<td style='width:30px'>".$_SESSION['lang']['noreferensi']."</td>"; 
				$tab.="<td style='width:30px'>: ".$param['notransaksi']."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$cellpadding=2;
		$cellspacing=0;
		
		// if($tipetransaksiht=='M'){
		// 	$width='width:450px';
		// }else{
		// 	$width='';
		// }
		
		
		// exit("Error:$width");
	
		$tab.="<table width='100%' style='font-size:".$fontsize.";' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			$tab.="<thead>";
				$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nourut']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['noakun']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nodok']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['tglterima']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['tanggaljatuhtempo']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['keterangan']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['control']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['tipe']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['jumlah']."</td>"; 
				$tab.="</tr>";	
			$tab.="</thead>";
			
			$tab.="<tr>";
			$tab.="<td colspan=9>&nbsp;</td>"; 
			$tab.="</tr>";
			$optketerangan =  makeOption($dbname,'keu_5keterangan','id_ket,keterangan');
			#= query pakai kasbankdt
			
			if($novoucherht==''){
				$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
			} else {	
				if($noreferensiht==''){
					$str = "select * from ".$dbname.".keu_kasbankdt where 
					notransaksi in ('".implode("','",$listnotransaksi)."')";
				}else{
					$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
				}
			}
			// echo $str;exit();
			
			
			
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$tab.="<tbody>";
			while($bar=$res->fetch()){
				
				if($kodept[$bar['pemilikhutang1']]==$kodept[$kodeorght])$jenisinduk='intra'; else $jenisinduk='inter';
				
				$whereNocaco = "jenis='".$jenisinduk."' and kodeorg='".$bar['pemilikhutang1']."'";
				$query = selectQuery($dbname,'keu_5caco','akunpiutang',$whereNocaco);
				$noKon = fetchData($query);
				$noakuncaco = $noKon[0]['akunpiutang'];
				// exit("Error:".$bar['pemilikhutang1']._.$kodeorght);
				
				if($bar['pemilikhutang1']!=''){
					if($bar['pemilikhutang1']!=$kodeorght){
						$bar['noakun']=$noakuncaco;
					}
				}
				
				$createbydt=$bar['createby'];
				
				$tipe='';
				if($bar['keterangan1']!=''){
					if($tipetransaksiht=='K'){
						$tipe='AP';
					}else{
						$tipe='AR';
					}
				}
				
				@$no+=1;
				$tab.="<tr>";
				$tab.="<td style=font-size:0.9em align=center valign=top>".$no."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$bar['noakun']."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$bar['keterangan1']."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$tglterima[$bar['keterangan1']]."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$tgljthtmpo[$bar['keterangan1']]."</td>"; 
				$tab.="<td style=font-size:0.9em align=left valign=top>".$nmakun[$bar['noakun']]."<br>".$bar['keterangan2']."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$bar['nodok']."</td>"; 
				$tab.="<td style=font-size:0.9em align=center valign=top>".$tipe."</td>"; 
				$tab.="<td style=font-size:0.9em align=right valign=top>".number_format($bar['jumlah'],2)."</td>"; 
				$tab.="</tr>";
				@$totaldt+=$bar['jumlah'];
			}
			$tab.="</tbody>";
				// $tab.="</table>";
				// $tab.="<table style='font-size:".$fontsize."' width=100% border=1 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				
				$tab.="<tr>";
				$tab.="<td colspan=9 height=50px>&nbsp;</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr width=60%>";
				$tab.="<td colspan=8 align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah'].":</td>"; 
				$tab.="<td  align=right style='width:10px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totaldt,2)."</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
				$tab.="<td align=left colspan=3 valign=top>".$noakunht."<br>".$nmakun[$noakunht]."</td>"; 
				$tab.="<td align=left  valign=top>".$nmbank[$kodebanksetup]."&nbsp;&nbsp;".$atasnamasetup."&nbsp;&nbsp;".$norekeningsetup."</td>"; 
				$isi="";
				if($nocekht!=''){
					$isi="".$_SESSION['lang']['nomor']." : ".$nocekht."";
				}
				
				$tab.="<td align=left valign=top>".$_SESSION['lang']['tipe']." : ".$cgttuht."<br>".$isi."</td>"; 
				$tab.="<td align=right colspan=3 valign=top>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah']."</td>"; 
				$tab.="<td  align=right valign=top>".number_format($jumlahht,2)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			$tab.="<td colspan=5 align=right valign=right><td colspan=4 >Terbilang : (".terbilang($jumlahht,2).' rupiah'.")</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=9>&nbsp;</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		// if(getNamaOrg($kodeorght,'tipe')=='HOLDING'){			
			// $tab.="<table style='width:100%;font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				// $tab.="<tr>";
					// $tab.="<td align=center style='width:33%;border-top:0.5px solid #000000;border-left:0.5px solid #000000;'>".$_SESSION['lang']['persetujuan']."</td>"; 
					// $tab.="<td align=center style='width:33%;border-top:0.5px solid #000000;border-left:0.5px solid #000000;'>".$_SESSION['lang']['dibuatoleh']."</td>";
					// $tab.="<td align=center style='width:33%;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['penerima']."</td>"; 
				// $tab.="</tr>";
				// $tab.="<tr>";
					// #ambil approval
					// $aprvkar = "";
					// $str = "select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."' order by level asc";
					// $resapp = fetchdata($str);
					// $jlhapp =  count($resapp);
					
					// $tab.="<td style='height:50px;text-align:center;color:gray;font-size:10px;border-left:0.5px solid #000000;'><i>".($jlhapp>0?'ELECTRONICALLY SIGNED BY':'')."<i></td>"; 			
					// $tab.="<td style='text-align:center;color:gray;font-size:10px;border-left:0.5px solid #000000;'><i>".($createbyht!=''?'ELECTRONICALLY SIGNED BY':'')."<i></td>"; 
					// $tab.="<td style='text-align:center;color:gray;font-size:10px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'><i>".($aprvkar!=''?'ELECTRONICALLY SIGNED BY':'')."<i></td>"; 
				// $tab.="</tr>";
				
				// $tab.="<tr>";
					// $tab.="<td style='border-bottom:0.5px solid #000000;text-align:center;border-left:0.5px solid #000000;'>";
						// $no=0;
						// foreach($resapp as $bar){
							// $no++;
							// if($no>1){
								// $tab.="<br>".$bar['level'].". ".getKary($bar['karyawanid']);
							// }else{							
								// $tab.=$bar['level'].". ".getKary($bar['karyawanid']);
							// }
						// } 
					// $tab.="</td>"; 			
					// $tab.="<td style='border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;text-align:center;'>1. ".getKary($createbyht)."<br>2. ".getKary($createbydt)."<br>3. ".getKary($kasirht)."</td>";
					// $tab.="<td style='border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;text-align:center;'>".$bayarkepada."</td>"; 
				// $tab.="</tr>";
			// $tab.="</table>";	
		// }else{
			$sql = "SELECT * FROM ".$dbname.".approval WHERE notransaksi='".$param['notransaksi']."'";
			$res = fetchData($sql, "OBJECT");

			foreach($res as $val) {
				$approvaldata[$val->jenispesetujuan][$val->level] = $val->karyawanid; 
			}

			$karypembuat = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$createbyht."'");

			$tab.="<table style='width:100%;font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
				$tab.="<tr>";
					$tab.="<td align=center>".$_SESSION['lang']['persetujuan']."</td>"; 
					$tab.="<td style='width:50px'>&nbsp;</td>"; 
					$tab.="<td align=center>".$_SESSION['lang']['diperiksaoleh']."</td>"; 
					$tab.="<td style='width:50px'>&nbsp;</td>"; 
					$tab.="<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>";
					$tab.="<td style='width:50px'>&nbsp;</td>"; 
					$tab.="<td align=center>".$_SESSION['lang']['penerima']."</td>"; 
				$tab.="</tr>";

				$tab.="<tr>";
					$tab.="<td style='height:50px;border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
					$tab.="<td align=center></td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
					$tab.="<td align=center></td>"; 				
					$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
					$tab.="<td align=center></td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
				$tab.="</tr>";

				foreach($approvaldata as $js => $val):
					foreach($val as $level => $karyawanid):
						$tab.="<tr>";
							$tab.="<td style='border-bottom:0.5px solid #fff;' align=center>".$nmkaryawan[$karyawanid]."</td>"; 
							$tab.="<td align=center></td>"; 
							$tab.="<td style='border-bottom:0.5px solid #fff;' align=center>".$nmkaryawan[$karyawanid]."</td>";
							$tab.="<td align=center></td>"; 				
							$tab.="<td style='border-bottom:0.5px solid #fff;' align=center>".$karypembuat[$createbyht]."</td>"; 
							$tab.="<td align=center></td>"; 
							$tab.="<td style='border-bottom:0.5px solid #fff;' align=center>&nbsp;</td>"; 
						$tab.="</tr>";
					endforeach;
				endforeach;
			$tab.="</table>";	
		//}
			
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		// $a=array(0,0,2150,1390);
		// $dompdf->setPaper('A5', 'landscape');
		// $dompdf->setPaper($a, 'landscape');
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("PrintKASBANK_".$param['notransaksi'],array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;
	
	case'pdfnewlama':
		$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tanggalht=$bar['tanggal'];
			$novoucherht=$bar['novoucher'];
			$noakunht=$bar['noakun'];
			$rekeninght=$bar['rekening'];
			$cgttuht=$bar['cgttu'];
			$nocekht=$bar['nocek'];
			$jumlahht=$bar['jumlah'];
			$tipetransaksiht=$bar['tipetransaksi'];
			$kodeorght=$bar['kodeorg'];
			$bayarkepada=$bar['bayarkepada'];
		
		$str = "select * from ".$dbname.".keu_5akunbank where noakun='".$rekeninght."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$norekeningsetup=$bar['rekening'];
			$kodebanksetup=$bar['namabank'];
			$atasnamasetup=$bar['atasnama'];
			
		#= data dt
		
		$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and kodesupplier!=''";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();			
			$kodesupplier=$bar['kodesupplier'];	

		#= supplier
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namasupplier=$bar['namasupplier'];		
		
			
		$cellpadding=2;	
		
		$tab="<style>
				@page {
					margin-top: 25px;
					margin-left: 30px;
					margin-right: 50px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}
				
				footer {
					position: fixed; 
					bottom: -20px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}
				
			</style>";
		// $tab = '';
			
			
		if($tipetransaksiht=='M'){
			$namavoucher='BUKTI MASUK';
		}else{
			$namavoucher='BUKTI PENGELUARAN';
		}			
		
		$cellpadding=1;
		$fontsize='10px';
		$tab.="<table width=115% style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding.">";	
			$tab.="<tr>";
				$tab.="<td colspan=4><b>".$nmorg[$kodeorght]."</b></td>"; 
				$tab.="<td colspan=2 align=left><b>".$namavoucher."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=4></td>";  
				$tab.="<td>".$_SESSION['lang']['novoucher']."</td>"; 
				$tab.="<td>: ".$novoucherht."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				if($tipetransaksiht=='M'){
					$tab.="<td style='width:30px'>Terima Dari</td>"; 
					$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
				}else{
					$tab.="<td style='width:30px'>".$_SESSION['lang']['bayarke']."</td>"; 
					$tab.="<td style='width:200px' colspan=3>: ".$bayarkepada."</td>"; 
				}
				$tab.="<td style='width:30px'>".$_SESSION['lang']['tanggal']."</td>"; 
				$tab.="<td style='width:30px'>: ".tanggalnormal($tanggalht)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=4></td>"; 
				$tab.="<td style='width:30px'>".$_SESSION['lang']['noreferensi']."</td>"; 
				$tab.="<td style='width:30px'>: ".$param['notransaksi']."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$cellpadding=2;
		$cellspacing=0;
		
		// if($tipetransaksiht=='M'){
		// 	$width='width:450px';
		// }else{
		// 	$width='';
		// }
		
		
		// exit("Error:$width");
	
		$tab.="<table style='font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing." width=100%>";	
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nourut']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['noakun']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['nodok']."</td>"; 
				$tab.="<td style='width=100%;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['keterangan']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['control']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['tipe']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$_SESSION['lang']['jumlah']."</td>"; 
			$tab.="</tr>";	
			
			$tab.="<tr>";
				$tab.="<td colspan=7>&nbsp;</td>"; 
			$tab.="</tr>";
			$optketerangan =  makeOption($dbname,'keu_5keterangan','id_ket,keterangan');
			#= query pakai kasbankdt
			$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$tipe='';
				if($bar['keterangan1']!=''){
					$tipe='AP';
				}
				@$no+=1;
				$tab.="<tr>";
				$tab.="<td align=center valign=top>".$no."</td>"; 
				$tab.="<td align=center valign=top>".$bar['noakun']."</td>"; 
				$tab.="<td align=center valign=top>".$bar['keterangan1']."</td>"; 
				$tab.="<td align=left valign=top>".$nmakun[$bar['noakun']]."<br>".$bar['keterangan2']."</td>"; 
				$tab.="<td align=center valign=top>".$bar['nodok']."</td>"; 
				$tab.="<td align=center valign=top>".$tipe."</td>"; 
				$tab.="<td  align=right valign=top>".number_format($bar['jumlah'],2)."</td>"; 
				$tab.="</tr>";
				@$totaldt+=$bar['jumlah'];
			}
			// $tab.="</table>";
			
			
			// $tab.="<table style='font-size:".$fontsize."' width=100% border=1 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
		
			$tab.="<tr>";
				$tab.="<td colspan=7 height=50px>&nbsp;</td>"; 
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td colspan=6 align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah'].":</td>"; 
				$tab.="<td  align=right style='width:10px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totaldt,2)."</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$tab.="<td align=left colspan=3 valign=top>".$noakunht."<br>".$nmakun[$noakunht]."</td>"; 
				$tab.="<td align=left  valign=top>".$nmbank[$kodebanksetup]."&nbsp;&nbsp;".$atasnamasetup."&nbsp;&nbsp;".$norekeningsetup."</td>"; 
				$isi="";
				if($nocekht!=''){
					$isi="".$_SESSION['lang']['nomor']." : ".$nocekht."";
				}
				
				$tab.="<td align=left valign=top>".$_SESSION['lang']['tipe']." : ".$cgttuht."<br>".$isi."</td>"; 
				$tab.="<td align=right valign=top>".$_SESSION['lang']['total']." ".$_SESSION['lang']['jumlah']."</td>"; 
				$tab.="<td  align=right valign=top>".number_format($jumlahht,2)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=7>&nbsp;</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<table style='width:100%;font-size:".$fontsize."' border=0 cellpadding=".$cellpadding." cellspacing=".$cellspacing.">";	
			$tab.="<tr>";
				$tab.="<td align=center>".$_SESSION['lang']['persetujuan']."</td>"; 
				$tab.="<td style='width:50px'>&nbsp;</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['diperiksaoleh']."</td>"; 
				$tab.="<td style='width:50px'>&nbsp;</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['dibuatoleh']."</td>";
				$tab.="<td style='width:50px'>&nbsp;</td>"; 
				$tab.="<td align=center>".$_SESSION['lang']['penerima']."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='height:50px;border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
				$tab.="<td align=center></td>"; 
				$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>";
				$tab.="<td align=center></td>"; 				
				$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
				$tab.="<td align=center></td>"; 
				$tab.="<td style='border-bottom:0.5px solid #000000;'>&nbsp;</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
			
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		// $a=array(0,0,2150,1390);
		// $dompdf->setPaper('A5', 'landscape');
		// $dompdf->setPaper($a, 'landscape');
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("PrintKASBANK_".$param['notransaksi'],array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;
	
	
	
	case'pdf3':
	
		class PDF extends FPDF {
            function Header() {
            }
            function Footer() {
            }
        }
		
		$pdf=new PDF('P','mm','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
       
        $pdf->AddPage();
		
        $pdf->SetFont('Arial','',8);
		
		$height = 5;
		
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell($width,$height,$_SESSION['org']['namaorganisasi'],0,1,'L',1);
		$pdf->Cell($width,$height,'JAKARTA',0,1,'L',1);
		$pdf->SetFillColor(220,220,220);
		
		if($param['tipetransaksi']=='M') {
			$pdf->Cell($width,$height,'VOUCHER PENERIMAAN KAS',1,1,'C',1);
		}else{
			$pdf->Cell($width,$height,'VOUCHER PENGELUARAN KAS',1,1,'C',1);
		}
		
		
		
		$pdf->Ln(2);
		
		$str="select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$noakunht=$bar['noakun'];
			$catatanht=$bar['keterangan'];
			$jumlahht=$bar['jumlah'];
			$notransaksi=$bar['notransaksi'];
			$tglvoucherht=$bar['tanggalpengajuan'];
			$novoucherht=$bar['novoucher'];
			$hutangunitht=$bar['hutangunit'];
			$noakunhutanght=$bar['noakunhutang'];
			$bayarkepada=$bar['bayarkepada'];
			
			
		if($bar['posting']==1){
			$posting='POSTING';
		}else{
			$posting='NOT POSTING';
		}
		
	
		if($param['tipetransaksi']=='M') {
			$datahdb= $bar['jumlah'];
			$datahkr=0;
		} else {
			$datahkr=$bar['jumlah'];
			$datahdb=0;
		}
		$tdatah=$bar['jumlah'];
		
			
		#bentuk jatuh tempo dan data supplier untuk pengeluaran 
		
		$str="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$noinvice=$bar['keterangan1'];
			@$kodecustomer=$bar['kodecustomer'];
			$nmkar="";
			$karidpenerima=array();
			if(intval($bar['nik'])!=0){
				$karidpenerima=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['nik']."'");
				@$nmkar=$karidpenerima[$bar['nik']];
			}
			
			
		$str="select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$namacus=$bar['namacustomer'];	
			
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvice."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$kodesupplier=$bar['kodesupplier'];
			@$tgljatuhtempo=tanggalnormal($bar['jatuhtempo']);
			if($tgljatuhtempo=='--'){
				@$tgljatuhtempo='';
			}
		
		$str="select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$namasupplier = $bar['namasupplier'];
		/*if(empty($namacus) or $namacus=='' or is_null($namacus)){
			if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
				@$namasup=$bar['namasupplier'];
			}else{
				@$namasup=$bayarkepada;	
			}
		}else{
			@$namasup=$namacus;
		}*/
		$namasup=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
			if($nmkar!=''){
				$namasup=$nmkar;
			}elseif($namacus!=''){
				@$namasup=$namacus;
			}elseif($namasupplier!=''){
				@$namasup=$namasupplier;
			}
		}
		@$atasnama=$bar['an'];
		@$banksup=$bar['bank'];
		@$rekeningsup=$bar['rekening'];
			
		#untuk tipe masuk sumber dari invoice penagihan
		// $str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$noinvice."'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// @$kodecustomer=$bar['kodecustomer'];

		$str="select * from ".$dbname.".pmn_4customercontact where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar2=$res->fetch();
		@$namacpcus=$bar2['nama'];	
		$penerima=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){	
			if($nmkar!=''){
				$penerima=$nmkar;
				@$atasnamacp=$nmkar;
			}elseif($namacus!=''){
				@$penerima=$namacus;
				@$atasnamacp=$namacpcus;
			}elseif($namasupplier!=''){
				@$penerima=$namasupplier;
			}
		}
		/*if(empty($namacus) or $namacus=='' or is_null($namacus)){
			if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
				@$penerima=$nmkar[$karidpenerima];
			}else{
				@$penerima=$bayarkepada;	
				
			}
			@$atasnamacp=$nmkar[$karidpenerima];
		}else{
			if(empty($bayarkepada) or $bayarkepada == ""){
				@$penerima=$namacus;
			}else{
				@$penerima=$bayarkepada;	
			}
			@$penerima=$namacus;
			
			@$atasnamacp=$namacpcus;
		}*/

		//exit("warning".$namacus."/".$bayarkepada);	
		$str="select * from ".$dbname.".keu_5caco";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$akunrk[$bar['akunpiutang']]=$bar['akunpiutang'];
			$akunrk[$bar['akunhutang']]=$bar['akunhutang'];
		}

		// echo"<pre>";
		// print_r($akunrk);
		// echo"</pre>";
			
		$numformat=2;
		$width=47.5;
		$pdf->SetFillColor(255,255,255);
		$pdf->Cell($width,$height,'NO. DOKUMEN','TL',0,'L',1);
		$pdf->Cell($width,$height,': '.$notransaksi,'T',0,'L',1);
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'DIBAYARKAN KEPADA','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$namasup,'RT',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'DITERIMA DARI','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$penerima,'RT',1,'L',1);
		}
		
		$pdf->Cell($width,$height,'NO. VOUCHER','L',0,'L',1);
		$pdf->Cell($width,$height,': '.$novoucherht,0,0,'L',1);
		$pdf->Cell($width,$height,'JUMLAH',0,0,'L',1);
		$pdf->Cell($width,$height,': '.number_format($jumlahht,$numformat),'R',1,'L',1);
		
		
		$pdf->Cell($width,$height,'TGL. VOUCHER','L',0,'L',1);
		$pdf->Cell($width,$height,': '.tanggalnormal($tglvoucherht),0,0,'L',1);
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'ATAS NAMA',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$atasnama,'R',1,'L',1);
			// $pdf->Cell($width,$height,': ','R',1,'L',1);
		}else{
			
			$pdf->Cell($width,$height,'ATAS NAMA',0,0,'L',1);
			$pdf->Cell($width,$height,': ','R',1,'L',1);
			// $pdf->Cell($width,$height,': '.$atasnamacp,'R',1,'L',1);
	
		}
		
		$pdf->Cell($width,$height,'NAMA BANK','L',0,'L',1);
		$pdf->Cell($width,$height,': '.$nmakun[$noakunht],0,0,'L',1);
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'NAMA BANK',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$banksup,'R',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'',0,0,'L',1);
			$pdf->Cell($width,$height,'','R',1,'L',1);
		}
		
		$pdf->Cell($width,$height,'NO. ACC','LB',0,'L',1);
		$pdf->Cell($width,$height,': '.$noakunht,'B',0,'L',1);
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'NO. ACC','B',0,'L',1);
			$pdf->Cell($width,$height,': '.$rekeningsup,'RB',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'','B',0,'L',1);
			$pdf->Cell($width,$height,'','RB',1,'L',1);
		}
		
		$pdf->Ln();
		
		$pdf->Cell(10,$height,'NO.',1,0,'C',1);
		$pdf->Cell(30,$height,'NO AKUN',1,0,'C',1);
		$pdf->Cell(90,$height,'NAMA AKUN',1,0,'C',1);
		$pdf->Cell(30,$height,'DEBIT',1,0,'C',1);
		$pdf->Cell(30,$height,'KREDIT',1,1,'C',1);
		
		
		$no=0;
		if($datahdb > 0){
			$no=1;
			$pdf->Cell(10,$height,$no,1,0,'C',1);
			$pdf->Cell(30,$height,$noakunht,1,0,'L',1);
			$pdf->Cell(90,$height,$nmakun[$noakunht],1,0,'L',1);
			$pdf->Cell(30,$height,number_format($datahdb,$numformat),1,0,'R',1);
			$pdf->Cell(30,$height,number_format($datahkr,$numformat),1,1,'R',1);
		}
	
		$str="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			if($param['tipetransaksi']=='M' and $bar['jumlah']>0) {
				$datadkr=$bar['jumlah'];
                $dataddb=0;
			}else if($param['tipetransaksi']=='K' and $bar['jumlah']<0){
				$datadkr=$bar['jumlah']*-1;
                $dataddb=0;
			}else if($param['tipetransaksi']=='M' and $bar['jumlah']<0){
				$dataddb=$bar['jumlah']*-1;
				$datadkr=0;
			}else{
				$dataddb=$bar['jumlah'];
				$datadkr=0;
			}
			
			if(in_array($bar['noakun'],$akunrk) and $hutangunitht==1){
				$bar['noakun']=$noakunhutanght;
			}
			
			$no++;
			$pdf->Cell(10,$height,$no,1,0,'C',1);
			$pdf->Cell(30,$height,$bar['noakun'],1,0,'L',1);
			$pdf->Cell(90,$height,$nmakun[$bar['noakun']],1,0,'L',1);
			$pdf->Cell(30,$height,number_format($dataddb,$numformat),1,0,'R',1);
			$pdf->Cell(30,$height,number_format($datadkr,$numformat),1,1,'R',1);
		}
		
		if($datahdb <= 0){
			$no++;
			$pdf->Cell(10,$height,$no,1,0,'C',1);
			$pdf->Cell(30,$height,$noakunht,1,0,'L',1);
			$pdf->Cell(90,$height,$nmakun[$noakunht],1,0,'L',1);
			$pdf->Cell(30,$height,number_format($datahdb,$numformat),1,0,'R',1);
			$pdf->Cell(30,$height,number_format($datahkr,$numformat),1,1,'R',1);
			
		}

		
		for($i=($no+1);$i<=6;$i++){
			$no++;
			$pdf->Cell(10,$height,$no,1,0,'C',1);
			$pdf->Cell(30,$height,'',1,0,'C',1);
			$pdf->Cell(90,$height,'',1,0,'C',1);
			$pdf->Cell(30,$height,'',1,0,'C',1);
			$pdf->Cell(30,$height,'',1,1,'C',1);
		}
		$pdf->Cell(130,$height,'TOTAL : ',1,0,'L',1);
		$pdf->Cell(30,$height,number_format($tdatah,$numformat),1,0,'R',1);
		$pdf->Cell(30,$height,number_format($tdatah,$numformat),1,1,'R',1);
		
		
		$pdf->MultiCell(190,$height,'TERBILANG : '.terbilang($tdatah,1),1,1,'L',1);
		
		
		$yawalcatatan=$pdf->GetY();
		$pdf->MultiCell(130,$height,'CATATAN : '.$catatanht,'L','T',0);
		$pdf->MultiCell(130,$height,'Terbilang : '.$catatanht,'L','T',0);
		$yakhircatatan=$pdf->GetY();
		
		//buat total y catatan
		$tycatatan=$yakhircatatan-$yawalcatatan;
		
	
		$pdf->SetXY(140,$yawalcatatan);
		$pdf->Cell(30,$height*2,'JATUH TEMPO',1,0,'L',1);
		$pdf->Cell(30,$height*2,$tgljatuhtempo,1,1,'L',1);
		$yakhirjt=$pdf->GetY();
		
		//total y jatuh tempo
		$tyjt=$yakhirjt-$yawalcatatan;
	
		//if untuk menyamakan total kotak catatan dengan posting 
		if($tycatatan<=($tyjt+$height)){
			$heightposting=$height*2;
		}else{
			$heightposting=$tycatatan-$tyjt;
		}
		
		$pdf->SetXY(140,$yakhirjt);
		$pdf->Cell(30,$heightposting,'POSTING',1,0,'L',1);
		$pdf->Cell(30,$heightposting,$posting,1,1,'L',1);
		$yakhirposting=$pdf->GetY();
		
		// untuk line jika catatan sedikit (koordinat y catatan kurang dari y posting posting)
		$pdf->Line(10,$yakhirposting,140,$yakhirposting);
		$pdf->Line(10,$yawalcatatan,10,$yakhirposting);
		
		$pdf->Ln(5);
		
		$widthttd='38';
		$rowheigt=2;
		
		$totalheight = (($height*3)+(($height*$rowheigt)*3));
		
		if((($totalheight+$pdf->GetY()) > 262.00125))
		{
			$pdf->AddPage();
		}
		
		// echo $pdf->GetY(); 247.00125
		
		$pdf->Cell($widthttd,$height,'DIAJUKAN :',1,0,'C',1);
		$pdf->Cell($widthttd,$height,'DIPERIKSA :',1,0,'C',1);
		$pdf->Cell($widthttd*2,$height,'DISETUJUI :',1,0,'C',1);
		$pdf->Cell($widthttd,$height,'DITERIMA :',1,1,'C',1);
	
		$pdf->Cell($widthttd,$height*$rowheigt,'','LRT',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LRT',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LTR',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LTR',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LTR',1,'C',1);
	
		//print_r($_SESSION['empl']['tipelokasitugas']);
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$pdf->Cell($widthttd,$height,'STAF','LRB',0,'C',1);
			$pdf->Cell($widthttd,$height,'DEPT. HEAD','LRB',0,'C',1);
		}else{
			$pdf->Cell($widthttd,$height,'','LR',0,'C',1);
			$pdf->Cell($widthttd,$height,'','LR',0,'C',1);
		}
		
		// echo $pdf->GetY();262.00125
		
		$pdf->Cell($widthttd,$height,'','LR',0,'C',1);
		$pdf->Cell($widthttd,$height,'','LR',0,'C',1);
		$pdf->Cell($widthttd,$height,'','LR',1,'C',1);
		
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$pdf->Cell($widthttd,$height*$rowheigt,'','LRT',0,'C',1);
			$pdf->Cell($widthttd,$height*$rowheigt,'','LRT',0,'C',1);
		}else{
			$pdf->Cell($widthttd,$height*$rowheigt,'','LR',0,'C',1);
			$pdf->Cell($widthttd,$height*$rowheigt,'','LR',0,'C',1);
		}
		
		$pdf->Cell($widthttd,$height*$rowheigt,'','LR',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LR',0,'C',1);
		$pdf->Cell($widthttd,$height*$rowheigt,'','LR',1,'C',1);
	
	if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
		$pdf->Cell($widthttd,$height,'SECTION HEAD','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'DIV. HEAD','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'DIREKTUR','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'CEO','LRB',0,'C',1);
	}else{
		$pdf->Cell($widthttd,$height,'KASIR','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'KTU','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'MANAGER','LRB',0,'C',1);
		$pdf->Cell($widthttd,$height,'','LRB',0,'C',1);
	}
		
		
		$pdf->Cell($widthttd,$height,'','LRB',1,'C',1);
		
		$pdf->Output();
		
	break;
	
	
	
	

    case 'pdf2':

       class PDF extends FPDF {
            function Header() {
                global $conn;
                global $dbname;
                global $userid;
                global $notransaksi;
                global $kodevhc;
                global $posting;
                global $kodept;
				global $param;
                global $owlPDO;
				
				//ambil nama pt
				$arrHead = setheadreport(substr($param['kodeorg'],0,4));
                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 5;
                $path=$arrHead['logo'];
                $this->Image($path,$this->lMargin,($this->tMargin-8),0,25);
                $this->SetFont('Arial','B',9);
                $this->SetFillColor(255,255,255);	
                $this->SetX(45);   
                $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
                $this->SetX(45); 		
                $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
                $this->SetX(45); 			
                $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
                $this->Line($this->lMargin,$this->tMargin+($height*4),
				$this->lMargin+$width,$this->tMargin+($height*4));
                $this->Ln();
            }

            function Footer() {
            }
        }
		
		
		$pdf=new PDF('P','mm','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 5;

        $pdf->AddPage();

        $res=$owlPDO->query("select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' ");
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=  $res->fetch();
			$noakun=$bar['noakun'];
			$mtuang=$bar['matauang'];
		if(substr($noakun,0,5)=='11102'){
			if($bar['tipetransaksi']=='K'){
				//$title = "BANK PAYMENT VOUCHER";
				$title = "Pengeluaran Bank";
			}else{
				//$title = "BANK PAYMENT VOUCHER";
				$title = "Penerimaan Bank";
			}
		}else{
			if($bar['tipetransaksi']=='K'){
				//$title = "CASH RECEIPT VOUCHER";
				$title = "Pengeluaran Kas";
			}else{
				//$title = "CASH PAYMENT VOUCHER";
				$title = "Penerimaan Kas";
			}
		}
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','B',12);
        
        $pdf->Cell($width,$height,strtoupper($title),0,1,'C',1);
		//$pdf->Ln(10);
		//$path='images/logopdfgrup.jpg';
		//$pdf->Image($path,160,3,40);	
		$pdf->SetFont('Arial','',8);
		$pdf->Cell(20,$height,'Paid To',0,0,'L',1);
		$pdf->Cell(5,$height,':',0,0,'L',1);
		$pdf->SetFont('Arial','U',8);
		$pdf->Cell(100,$height,'                                                                  ',0,0,'L',1);
		
			$pdf->SetFont('Arial','',8);
			$pdf->Cell(10,$height,'Date',0,0,'L',1);
			$pdf->Cell(5,$height,':',0,0,'L',1);
			$pdf->SetFont('Arial','U',8);
			$pdf->Cell(25,$height,tanggalnormal($bar['tanggal']).'                                            ',0,1,'L',1);
			$pdf->SetFont('Arial','',8);
			
		$pdf->Cell(20,$height,'Department',0,0,'L',1);
		$pdf->Cell(5,$height,':',0,0,'L',1);
		$pdf->SetFont('Arial','U',8);
		$pdf->Cell(100,$height,$nmorg[$bar['kodeorg']].'',0,0,'L',1);
		$pdf->SetFont('Arial','',8);
			$pdf->Cell(10,$height,'No.',0,0,'L',1);
			$pdf->Cell(5,$height,':',0,0,'L',1);
			$pdf->SetFont('Arial','U',8);
			$pdf->Cell(25,$height,$bar['notransaksi'].'                  ',0,1,'L',1);	
					
		$pdf->Ln();

		
		

			
		$pdf->SetFont('Arial','B',8);
		
        $pdf->SetFillColor(220,220,220);
		$pdf->Cell(10,$height,'No.',1,0,'C',1);
        $pdf->Cell(140,$height,'Description',1,0,'C',1);
        $pdf->Cell(40,$height,'Amount',1,1,'C',1);
		 
		$pdf->SetFillColor(255,255,255);
		 $pdf->SetFont('Arial','',8);
		$str="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$yawal=$pdf->GetY();
		$yawalgaris=$pdf->GetY();
		$xawal=$pdf->GetX();  
		
		while($bar=$res->fetch()){
			@$no+=1;
				$pdf->SetY($yawal);
			$pdf->Cell(10,$height,$no,0,0,'C',1);
				$yawalket=$pdf->GetY();
			$pdf->MultiCell(140,$height,$bar['keterangan2'],0);
				$yakhirket=$pdf->GetY();
			$pdf->SetXY($xawal+150,$yawalket);
				$pdf->Cell(40,$height,number_format($bar['jumlah']),0,0,'R',1);
			$pdf->Line($xawal,$yawalket,$xawal,$yakhirket);
			$pdf->Line($xawal+10,$yawalket,$xawal+10,$yakhirket);
			$pdf->Line($xawal+150,$yawalket,$xawal+150,$yakhirket);
			$pdf->Line($xawal+190,$yawalket,$xawal+190,$yakhirket);
			@$total+=$bar['jumlah'];
			$yawal=$yakhirket;
			
			
			
		}
		
		
		
		$pdf->Ln();
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(150,$height,'Total',1,0,'R',1);
		$pdf->Cell(40,$height,number_format($total),1,1,'R',1);
		$pdf->Ln();
		
		$pdf->Cell(30,$height,'Amount in words',0,0,'L',1);
		$pdf->Cell(5,$height,':',0,0,'L',1);
		$pdf->SetFont('Arial','u',8);
		$pdf->MultiCell(115,$height,terbilang($total,3). ' '.$nmMt[$mtuang],0);
		
   
		$pdf->Ln();


		#################################
		############## ttd ##############
		#################################
		
		//$pdf->SetFillColor(220,220,220);
		$pdf->SetFont('Arial','',8);
		
		
		
			$pdf->Cell(20/100*$width,$height,'Prepared by',1,0,'C',1);
			$pdf->Cell(20/100*$width,$height,'Checked by',1,0,'C',1);
			$pdf->Cell(20/100*$width,$height,'Acknowledged by',1,0,'C',1);
			$pdf->Cell(20/100*$width,$height,'Approved by',1,0,'C',1);
			$pdf->Cell(20/100*$width,$height,'Received by',1,1,'C',1);
			$pdf->Cell(20/100*$width,$height,'','TRL',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'','TRL',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'','TRL',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'','TRL',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'','TRL',0,'C',1);						
			
			
			
			for($i=0;$i<4;$i++) {
				$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
				$pdf->Ln();
			}
			
			$pdf->Cell(20/100*$width,$height,'(...........................)','BLR',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'(...........................)','BLR',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'(...........................)','BLR',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'(...........................)','BLR',0,'C',1);
			$pdf->Cell(20/100*$width,$height,'(...........................)','BLR',1,'C',1);
			
			$pdf->Ln(1);
			$pdf->SetFont('Arial','I',5);							
			$pdf->Cell(100/100*$width,$height,'*Please fill name and date of signature.','B',0,'L',1);
		
		$pdf->Output();
        break;


    case 'pdf':

		if(!class_exists('PDF')){
			class PDF extends FPDF
			{
				function Header()
				{
					global $conn;
					global $dbname;
					global $userid;
					global $notransaksi;
					global $kodevhc;
					global $posting;
					global $kodept;
					global $param;
					global $owlPDO;

					//ambil nama pt
					$arrHead = setheadreport(substr($param['kodeorg'],0,4));
					
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 5;
		//             $path=$arrHead['logo'];
		//             $this->Image($path,$this->lMargin,($this->tMargin-8),0,25);
		//             $this->SetFont('Arial','B',9);
		//             $this->SetFillColor(255,255,255);	
		//             $this->SetX(45);   
		//             $this->Cell($width-100,$height,$arrHead['nama'],0,1,'L');	 
		//             $this->SetX(45); 		
		//             $this->Cell($width-100,$height,$arrHead['alamat'],0,1,'L');	
		//             $this->SetX(45); 			
		//             $this->Cell($width-100,$height,"Tel: ".$arrHead['telepon'],0,1,'L');	
		//             $this->Line($this->lMargin,$this->tMargin+($height*4),
					// $this->lMargin+$width,$this->tMargin+($height*4));
		//             $this->Ln();
									
				}

				function Footer()
				{
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 12;
					$this->SetY(-20);
					$this->SetFont('Arial','I',7);
					$this->Cell(1,$height,'Page '.$this->PageNo(),'T',0,'L');
					$str = "Printed by ".$_SESSION['standard']['username']."[".$_SESSION['empl']['lokasitugas']."]".
							":".@$rPeriode['periode']." at ".date('d-m-Y H:i:s');
					$this->Cell($width-1,$height,$str,'T',0,'R');
				}
			}
		}
	
		
		$pdf=new PDF('P','mm','A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 5;

        $idapv=$owlPDO->query("select notransaksi,karyawanid,tanggal from ".$dbname.".approval where notransaksi='".$param['notransaksi']."'");
        $idapv->setFetchMode(PDO::FETCH_ASSOC);
        while($ddapv= $idapv->fetch()){
			
				$apv=$ddapv['karyawanid'];
				$tglap=tanggalnormald($ddapv['tanggal']);
				
			}

		//$optpembuat=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$userid."'");
     


        $pdf->AddPage();
		$norekeninght="";
        $iht=$owlPDO->query("select * from ".$dbname.".keu_kasbankht where notransaksi='".$param['notransaksi']."' ");
        $iht->setFetchMode(PDO::FETCH_ASSOC);
        $dht=  $iht->fetch();
		$noakunht=$dht['noakun'];
		$norekeninght=$dht['rekening'];
		if($norekeninght!=""){
			$sBankHeader="select a.rekening,a.atasnama,b.namabank from ".$dbname.".keu_5akunbank a left join ".$dbname.".keu_5daftarbank b on a.namabank=b.kodebank 
			              where noakun='".$norekeninght."'";
			$rBankHeader=fetchData($sBankHeader);
			$norekheader=$rBankHeader[0]['rekening'];
			$atasnamaheader=$rBankHeader[0]['atasnama'];
			$namabankheader=$rBankHeader[0]['namabank'];
		}

			$tgl=$dht['tanggalinput'];
			$tgl1=$dht['tanggal'];

		$catatanht=$dht['keterangan'];
		$jumlahht=$dht['jumlah'];
		$nobukti=$dht['nocek'];
		$notransaksi=$dht['notransaksi'];
		$tglvoucherht=$dht['tanggal'];
		$novoucherht=$dht['novoucher'];
		$hutangunitht=$dht['hutangunit'];
		$noakunhutanght=$dht['noakunhutang'];
		$bayarkepada=$dht['bayarkepada'];
		$norekpenerima=$dht['norekpenerima'];
		$namapenerima=$dht['namapenerima'];
		$nocek=$dht['nocek'];
		$userid=$dht['userid'];
		$namabank=$dht['namabank'];
		$optpembuat=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$userid."'");
		$optakr=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
		@$karpembuat=$optpembuat[$userid];
		@$karydiset=$optakr[$apv];
			
		if($dht['posting']==1){
			$posting='POSTING';
		}else{
			$posting='NOT POSTING';
		}
		
	
		if($param['tipetransaksi']=='M') {
			$datahdb= $dht['jumlah'];
			$datahkr=0;
		} else {
			$datahkr=$dht['jumlah'];
			$datahdb=0;
		}
		$tdatah=$dht['jumlah'];

		#bentuk jatuh tempo dan data supplier untuk pengeluaran 
		$str="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$noinvice=$bar['keterangan1'];
		@$kodecustomer=$bar['kodecustomer'];
		$nmkar="";
		$karidpenerima=array();
		if(intval($bar['nik'])!=0){
			$karidpenerima=  makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['nik']."'");
			@$nmkar=$karidpenerima[$bar['nik']];
		}
			
			
		$str="select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$namacus=$bar['namacustomer'];	
			
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$noinvice."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		@$kodesupplier=$bar['kodesupplier'];
		@$tgljatuhtempo=tanggalnormal($bar['jatuhtempo']);
		if($tgljatuhtempo=='--'){
			@$tgljatuhtempo='';
		}
		
		$str="select * from ".$dbname.".log_5supplier  where supplierid='".$kodesupplier."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		$namasupplier = $bar['namasupplier'];
		/*if(empty($namacus) or $namacus=='' or is_null($namacus)){
			if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
				@$namasup=$bar['namasupplier'];
			}else{
				@$namasup=$bayarkepada;	
			}
		}else{
			@$namasup=$namacus;
		}*/
		$namasup=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){
			if($namapenerima!=''){
				$namasup=$namapenerima;
			}
			elseif($nmkar!=''){
				$namasup=$nmkar;
			}elseif($namacus!=''){
				@$namasup=$namacus;
			}elseif($namasupplier!=''){
				@$namasup=$namasupplier;
			}
		}
		// @$atasnama=$bar['an'];
		// @$banksup=$bar['bank'];
		// @$rekeningsup=$bar['rekening'];

		$str="select * from ".$dbname.".pmn_4customercontact where kodecustomer='".$kodecustomer."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar2=$res->fetch();
		@$namacpcus=$bar2['nama'];	
		$penerima=$bayarkepada;
		if(empty($bayarkepada) or $bayarkepada == "" or is_null($bayarkepada)){	
			if($nmkar!=''){
				$penerima=$nmkar;
				@$atasnamacp=$nmkar;
			}elseif($namacus!=''){
				@$penerima=$namacus;
				@$atasnamacp=$namacpcus;
			}elseif($namasupplier!=''){
				@$penerima=$namasupplier;
			}
		}

		// if(substr($dht['noakun'],0,5)=='11102'){
		// 	if($dht['tipetransaksi']=='K'){
		// 		$title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['keluar'].")");
		// 	}else{
		// 		$title = strtoupper($_SESSION['lang']['bank']." (".$_SESSION['lang']['masuk'].")");
		// 	}
		// }else{
		// 	if($dht['tipetransaksi']=='K'){
		// 		$title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['keluar'].")");
		// 	}else{
		// 		$title = strtoupper($_SESSION['lang']['kas']." (".$_SESSION['lang']['masuk'].")");
		// 	}
		// }

		$idt=$owlPDO->query("select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' ");
        $idt->setFetchMode(PDO::FETCH_ASSOC);
        $ddt=  $idt->fetch();

        if($ddt['nodok']!=''){
        	$nodok=$ddt['nodok'];
        }else{
        	$nodok="-";
        }
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);

		#= ke pt
		#= nama pt
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodeorg']."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$kdpt=$bar['induk'];	
		
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$kdpt."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			@$namapt=$bar['namaorganisasi'];
		
        $pdf->Cell($width,$height,$namapt,0,1,'L',1);
		$pdf->Cell($width,$height,'JAKARTA',0,1,'L',1);
		$pdf->SetFillColor(220,220,220);
		
		if($param['tipetransaksi']=='M') {
			$pdf->Cell($width,$height,'VOUCHER PENERIMAAN KAS',1,1,'C',1);
		}else{
			$pdf->Cell($width,$height,'VOUCHER PENGELUARAN KAS',1,1,'C',1);
		}

		$pdf->Ln(2);

		$numformat=2;
		$width=40;
		$awalxkop=$pdf->GetX();
		$awalykop=$pdf->GetY();
		
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',8);
		$pdf->Cell($width,$height,'NO. VOUCHER','TL',0,'L',1);
		$pdf->Cell($width*3.75,$height,': '.$notransaksi,'TR',1,'L',1);
		// $pdf->Cell($width,$height,'NO. VOUCHER','L',0,'L',1);
		// $pdf->Cell($width*3.75,$height,': '.$novoucherht,'R',1,'L',1);
		
		#= kanan dipisah
		/*
		if($param['tipetransaksi']=='K') {
			$pdf->Cell($width,$height,'DIBAYARKAN KEPADA','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$namasup,'RT',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'DITERIMA DARI','T',0,'L',1);
			$pdf->Cell($width,$height,': '.$penerima,'RT',1,'L',1);
		}
		*/
		
		if ($tglvoucherht=='2000-00-00' || $tglvoucherht=='0000-00-00') {
			$tglvoucherht='';
		}else{
			$tglvoucherht=tanggalnormal($tglvoucherht);
		}
		if ($tgl=='2000-00-00' || $tgl=='0000-00-00') {
			$tgl='';
		}else{
			$tgl=tanggalnormal($tgl);
		}
		// if ($tglap=='2000-00-00' || $tglap=='0000-00-00') {
		// 	$tglap='';
		// } 
		if ($tgl1=='2000-00-00' || $tgl1=='0000-00-00') {
			$tgl1='';
		}else{
			$tgl1=tanggalnormal($tgl1);
		}
		
	 

		//$pdf->Cell($width,$height,'NO. DOKUMEN','L',0,'L',1);#posisi original
		$pdf->Cell($width,$height,'TGL. VOUCHER','L',0,'L',1);
		//$pdf->Cell($width,$height,': '.$novoucherht,0,0,'L',1);#posisi original
		$pdf->Cell($width*3.75,$height,': '.$tglvoucherht,'R',1,'L',1);
		
		/*
		#= kanan dipisah
		$pdf->Cell($width,$height,'JUMLAH',0,0,'L',1);
		$pdf->Cell($width,$height,': '.number_format($jumlahht,$numformat),'R',1,'L',1);
		*/

		$pdf->Cell($width,$height,'NAMA BANK','L',0,'L',1);
		$pdf->Cell($width*3.75,$height,': '.$namabankheader,'R',0,'L',1);
		if($param['tipetransaksi']=='K') {
			// $pdf->Cell($width,$height,'ATAS NAMA',0,0,'L',1);#posisi asli
			// $pdf->Cell($width,$height,': '.$atasnama,'R',1,'L',1);#posisi asli
			$pdf->Cell($width,$height,'',0,0,'L',1);
			$pdf->Cell($width,$height,' ','R',1,'L',1);
			// $pdf->Cell($width,$height,': ','R',1,'L',1);
		}else{
			
			// $pdf->Cell($width,$height,'ATAS NAMA',0,0,'L',1);#posisi asli
			// $pdf->Cell($width,$height,': ','R',1,'L',1);#posisi asli
			$pdf->Cell($width,$height,'',0,0,'L',1);
			$pdf->Cell($width,$height,'','R',1,'L',1);
			// $pdf->Cell($width,$height,': '.$atasnamacp,'R',1,'L',1);
	
		}
		
		//$pdf->Cell($width,$height,'NAMA BANK','L',0,'L',1);
		$pdf->Cell($width,$height,'NO REK','L',0,'L',1);#posisi asli
		$pdf->Cell($width*1.75,$height,': '.$norekheader,0,0,'L',1);
		if($param['tipetransaksi']=='K') {
			// $pdf->Cell($width,$height,'NAMA BANK',0,0,'L',1);#posisi asli
			// $pdf->Cell($width,$height,': '.$banksup,'R',1,'L',1);#posisi asli
			$pdf->Cell($width,$height,'',0,0,'L',1);
			$pdf->Cell($width,$height,'','R',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'',0,0,'L',1);
			$pdf->Cell($width,$height,'','R',1,'L',1);
		}
		
		//$pdf->Cell($width,$height,'NO. ACC','LB',0,'L',1);
		$pdf->Cell($width,$height,'ATAS NAMA','LB',0,'L',1);#ATAS NAMA HEADER
		$pdf->Cell($width*1.75,$height,': '.$atasnamaheader,'B',0,'L',1);
		if($param['tipetransaksi']=='K') {
			// $pdf->Cell($width,$height,'NO. ACC','B',0,'L',1); #posisi asli
			// $pdf->Cell($width,$height,': '.$rekeningsup,'RB',1,'L',1); #posisi asli
			$pdf->Cell($width,$height,'','B',0,'L',1);
			$pdf->Cell($width,$height,'','RB',1,'L',1);
		}else{
			$pdf->Cell($width,$height,'','B',0,'L',1);
			$pdf->Cell($width,$height,'','RB',1,'L',1);
		}
		
		#= buat akhir y dikolom kiri untuk akhir dari kop
		$akhirxkop=$pdf->GetX();
		$akhirykop=$pdf->GetY();
		
		
		#=========================================== mulai kanan
		$pdf->SetXY($awalxkop+($width*2),$awalykop);

		if ($norekpenerima=='') {
			if($param['tipetransaksi']=='K') {
				$pdf->Cell($width,$height,'DIBAYARKAN KEPADA','T',0,'L',1);
				$pdf->Cell(2,$height,': ','T',0,'L',1);
				$pdf->MultiCell(($width*1.75), $height,$namasup, '0', 'L');
				// $pdf->Cell($width,$height,': '.$namasup,'RT',1,'L',1);
			}else{
				$pdf->Cell($width,$height,'DITERIMA DARI','T',0,'L',1);
				$pdf->Cell(2,$height,': ','T',0,'L',1);
				$pdf->MultiCell(($width*1.75), $height,$penerima, '0', 'L');
				// $pdf->Cell($width,$height,': '.$penerima,'RT',1,'L',1);
			}
		}

		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();


		//$pdf->Cell($width,$height,'TGL. VOUCHER','L',0,'L',1);#posisi asli
		if ($norekpenerima!='') {
			$pdf->Cell($width,$height,'JUMLAH','T',0,'L',1);
			$pdf->Cell($width*1.75,$height,': '.number_format($jumlahht,$numformat),'TR',1,'L',1);
		}else{
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'JUMLAH',0,0,'L',1);
			$pdf->Cell($width*1.75,$height,': '.number_format($jumlahht,$numformat),'R',1,'L',1);
		}
		

		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();


		$optNama=makeOption($dbname,"keu_5daftarbank","kodebank,namabank","kodebank='".$namabank."'");
		if ($namabank!='' && $norekpenerima=='') {
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'BANK PENERIMA',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$optNama[$namabank],0,1,'L',1);
		}

		/*$pdf->SetXY($akhirxket+($width*2),$akhiryket);
		$pdf->Cell($width,$height,'No.Bukti Bayar',0,0,'L',1);
		$pdf->Cell($width,$height,': '.$nobukti,'R',1,'L',1);*/

		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();

		if ($norekpenerima!='') {
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'NO. REKENING PENERIMA',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$norekpenerima,0,1,'L',1);
		}

		$optkodeBank=makeOption($dbname,"keu_5akunbank","rekening,namabank","rekening='".$norekpenerima."'");
		$optNamaBank=makeOption($dbname,"keu_5daftarbank","kodebank,namabank","kodebank='".$optkodeBank[$norekpenerima]."'");
		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();
		if ($optNamaBank[$optkodeBank[$norekpenerima]]!='') {
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'BANK PENERIMA',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$optNamaBank[$optkodeBank[$norekpenerima]],0,1,'L',1);
		}else{
			$optNama=makeOption($dbname,"keu_5daftarbank","kodebank,namabank","kodebank='".$namabank."'");
			if ($namabank!='') {
				$pdf->SetXY($akhirxket+($width*2),$akhiryket);
				$pdf->Cell($width,$height,'BANK PENERIMA',0,0,'L',1);
				$pdf->Cell($width,$height,': '.$optNama[$namabank],0,1,'L',1);
			}
		}
		
		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();

		if ($namapenerima!='') {
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'NAMA PENERIMA',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$namapenerima,0,1,'L',1);
		}
		
		$akhirxket=$pdf->GetX();
		$akhiryket=$pdf->GetY();

		if ($nobukti!='') {
			$pdf->SetXY($akhirxket+($width*2),$akhiryket);
			$pdf->Cell($width,$height,'NO BUKTI BAYAR',0,0,'L',1);
			$pdf->Cell($width,$height,': '.$nobukti,0,1,'L',1);
		}
		
		
		#=============================================== selesai kanan
		
		
		
		#= kembalikan akhir y mengikuti kiri
		$pdf->SetXY($akhirxkop,$akhirykop);
		
		
		$pdf->Ln();
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $pdf->SetFont('Arial','B',9);
        
  //       $pdf->Cell($width,$height,$title,0,1,'C',1);//ini nanti isi masuk / keluar , kas
		// $pdf->Cell(30,$height,$_SESSION['lang']['notransaksi'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->Cell($width-33,$height,$param['notransaksi'],0,1,'L',1);
		// $pdf->Cell(30,$height,$_SESSION['lang']['noinvoice'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->Cell($width-33,$height,$nodok,0,1,'L',1);
		
		// // $pdf->Cell(30,$height,$_SESSION['lang']['novoucher'],0,0,'L',1);
		// // $pdf->Cell(3,$height,':',0,0,'L',1);
		// // $pdf->Cell($width-33,$height,$dht['novoucher'],0,1,'L',1);
		
		// $pdf->Cell(30,$height,$_SESSION['lang']['cgttu'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->Cell($width-33,$height,$dht['cgttu'],0,1,'L',1);
		
		// if($dht['cgttu'] == 'Giro'){
		// 	$pdf->Cell(30,$height,'No. Giro',0,0,'L',1);
		// 	$pdf->Cell(3,$height,':',0,0,'L',1);
		// 	$pdf->Cell($width-33,$height,$dht['nocek'],0,1,'L',1);
		// }else if($dht['cgttu'] == 'Cheque'){
		// 	$pdf->Cell(30,$height,'No. Cek',0,0,'L',1);
		// 	$pdf->Cell(3,$height,':',0,0,'L',1);
		// 	$pdf->Cell($width-33,$height,$dht['nocek'],0,1,'L',1);
		// }
		
		// $pdf->Cell(30,$height,$_SESSION['lang']['matauang'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->Cell($width-33,$height,$dht['matauang'],0,1,'L',1);
		
		// $pdf->Cell(30,$height,$_SESSION['lang']['kurs'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->Cell($width-33,$height,number_format($dht['kurs'],0),0,1,'L',1);
		
		// $pdf->Cell(30,$height,$_SESSION['lang']['terbilang'],0,0,'L',1);
		// $pdf->Cell(3,$height,':',0,0,'L',1);
		// $pdf->MultiCell($width-33,$height,terbilang($dht['jumlah'],3). ' '.$nmMt[$dht['matauang']],0);
		
		
		
		//No. No. Akun Nama Akun Kode Kas Debet Kredit
        $pdf->SetFillColor(220,220,220);
        $pdf->Cell(10,$height,$_SESSION['lang']['nourut'],1,0,'C',1);
        $pdf->Cell(20,$height,'No. Akun',1,0,'C',1);
        $pdf->Cell(80,$height,$_SESSION['lang']['namaakun'],1,0,'C',1);
        $pdf->Cell(20,$height,'Kode Kas',1,0,'C',1);
        $pdf->Cell(30,$height,$_SESSION['lang']['debet'],1,0,'C',1);
        $pdf->Cell(30,$height,$_SESSION['lang']['kredit'],1,1,'C',1);
		
		$pdf->SetFillColor(255,255,255);
        $pdf->SetFont('Arial','',9);
        
		//prepare data
		if($param['tipetransaksi']=='M') {
			$datahdb= $dht['jumlah'];
		} else {
			$datahkr=$dht['jumlah'];
		}
		
		if($datahdb > 0){
			############################### head #################################
			############## buat baris pertama dulu untuk pembanding ##############
			######################################################################
			$height=5;
			$awalynmakun=$pdf->GetY();

			$whrakun=" noakun = '".$dht['noakun']."' ";
			$optAk = makeOption($dbname,'keu_5akun','noakun,namaakun',$whrakun);
			
			$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
			$pdf->MultiCell(80, $height, $optAk[$dht['noakun']], '0', 'L');
			$akhirynmakun=$pdf->GetY();
			$tinggiynmakun=$akhirynmakun-$awalynmakun;
			$heightakun=$tinggiynmakun;
			$pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
			
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			$pdf->Cell(20,$height,$dht['noakun'],'TRL',0,'R',1);
			$awalxlistnmakun=$pdf->GetX();
			$pdf->MultiCell(80,$height,$optAk[$dht['noakun']],'TRL','J');
			$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			@$pdf->Cell(20,$height,$dht['noaruskas'],'TRL',0,'C',1);
			@$pdf->Cell(30,$height,number_format($datahdb,2),'TRL',0,'R',1);
			@$pdf->Cell(30,$height,number_format($datahkr,2),'TRL',1,'R',1);
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
			
			########################################
			############## tutup head ##############
			########################################
		}
        
        #########################################
        ############## buat detail ##############
		#########################################
		$idtd=$owlPDO->query("select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'  order by jumlah desc");//group by noakun,noaruskas,keterangan2temp,tahun,bulan,keterangan1 
        $idtd->setFetchMode(PDO::FETCH_ASSOC);
        while($ddtd= $idtd->fetch()){
			if($param['tipetransaksi']=='M' and $ddtd['jumlah']>0) {
				$datadkr=$ddtd['jumlah'];
                $dataddb=0;
			}else if($param['tipetransaksi']=='K' and $ddtd['jumlah']<0){
				$datadkr=$ddtd['jumlah']*-1;
                $dataddb=0;
			}else if($param['tipetransaksi']=='M' and $ddtd['jumlah']<0){
				$dataddb=$ddtd['jumlah']*-1;
				$datadkr=0;
			}else{
				$dataddb=$ddtd['jumlah'];
				$datadkr=0;
			}

			

			
			##buat baris pertama dulu
            $height=5;
            
			$awalynmakun=$pdf->GetY();
            $pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
            @$pdf->MultiCell(80, $height, $optAkun[$ddtd['noakun']], '0', 'L');
            $akhirynmakun=$pdf->GetY();
            $tinggiynmakun=$akhirynmakun-$awalynmakun;
            $heightakun=$tinggiynmakun;
            $pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
            
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			@$pdf->Cell(20,$height,$ddtd['noakun'],'TRL',0,'R',1);
			@$awalxlistnmakun=$pdf->GetX();
			@$pdf->MultiCell(80,$height,$optAkun[$ddtd['noakun']],'TRL','J');
			@$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			$pdf->Cell(20,$height,$ddtd['noaruskas'],'TRL',0,'C',1);
			$pdf->Cell(30,$height,number_format($dataddb,2),'TRL',0,'R',1);
			$pdf->Cell(30,$height,number_format($datadkr,2),'TRL',1,'R',1);
			
			$pdf->Cell(10,$height,'','RL',0,'C',1);
			@$pdf->Cell(20,$height,'','RL',0,'R',1);
			@$awalxlistnmakun=$pdf->GetX();
			$awalynmakun=$pdf->GetY();
			@$pdf->MultiCell(80,$height,'No. Invoice : '.$ddtd['keterangan1'],'RL','J');
			@$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			$pdf->Cell(20,$height,'','RL',0,'C',1);
			$pdf->Cell(30,$height,'','RL',0,'R',1);
			$pdf->Cell(30,$height,'','RL',1,'R',1);
			
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
			$detailRekening="";
			if(($ddtd['kodesupplier']!='')&&(!is_null($ddtd['kodesupplier']))){
					$sRek="select * from ".$dbname.".log_5rekbank where supplierid='".$ddtd['kodesupplier']."' and matauang='".$ddtd['matauang']."'";
					// echo $sRek;
					// exit('warning');
					$rRek=fetchData($sRek);
					$detailRekening="\n"."Bank : ".$rRek[0]['bank']."\n"."Rekening : ".$rRek[0]['rekening']."\n"."Atas Nama : ".$rRek[0]['an'];	
			}
			
			
			if(($ddtd['keterangan2']!='')&&(!is_null($ddtd['keterangan2']))){
					$dataketerangan2=", ".$ddtd['keterangan2'];
			}
			
			
			if($ddtd['keterangan2temp'] != '' || $ddtd['keterangan2temp'] != null){
				$awalyket=$pdf->GetY();
				$pdf->SetX(100000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
				$pdf->MultiCell(80, $height,'Keterangan : '.$ddtd['keterangan2'].' '.$detailRekening.' '.$dataketerangan2, '0', 'L');
				$akhiryket=$pdf->GetY();
				$tinggiyket=$akhiryket-$awalyket;
				$heightket=$tinggiyket;
				$pdf->SetY($akhiryket-$tinggiyket);
				
				$pdf->Cell(10,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(20,$heightket,'','BRL',0,'R',1);
				$awalxlistket=$pdf->GetX();
				$pdf->MultiCell(80,$height,'Keterangan : '.$ddtd['keterangan2'].' '.$detailRekening.' '.$dataketerangan2,'BRL','L');
				$pdf->SetXY($awalxlistket+80, $awalyket);
				$pdf->Cell(20,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(30,$heightket,'','BRL',0,'R',1);
				$pdf->Cell(30,$heightket,'','BRL',1,'R',1);
			
				
				/*$pdf->Cell(10,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(20,$heightket,'','BRL',0,'R',1);
				$awalxlistket=$pdf->GetX();
				$pdf->MultiCell(80,$height,'KeteranganY : '.$ddtd['keterangan2'].$detailRekening.$dataketerangan2,'BRL','J');
				$pdf->SetXY($awalxlistket+80, $awalyket);
				$pdf->Cell(20,$heightket,'','BRL',0,'C',1);
				$pdf->Cell(30,$heightket,'','BRL',0,'R',1);
				$pdf->Cell(30,$heightket,'','BRL',1,'R',1);*/
			}
			
			if($pdf->GetY() > 240) {
				$akhirY=$akhirY-20;
				$akhirY=$pdf->GetY()-$akhirY;
				$akhirY=$akhirY+35;
				$pdf->AddPage();
			}
			
			@$totdtdb+=$dataddb;
            @$totdtkr+=$datadkr;
		}
		##########################################
        ############## tutup detail ##############
        ##########################################
        
		// if($datahdbx <= 0){
		if($datahdb <= 0){
			############################### head #################################
			############## buat baris pertama dulu untuk pembanding ##############
			######################################################################
			$height=5;
			$awalynmakun=$pdf->GetY();
			
			$pdf->SetX(10000);//di taro di 10000 agar sampai ujung jadi hilang dari kertas
			$pdf->MultiCell(80, $height, $optAkun[$dht['noakun']], '0', 'L');
			$akhirynmakun=$pdf->GetY();
			$tinggiynmakun=$akhirynmakun-$awalynmakun;
			$heightakun=$tinggiynmakun;
			$pdf->SetY($akhirynmakun-$tinggiynmakun);
			
			$no+=1;
			
			$awalxlist=$pdf->GetX();
			$awalylist=$pdf->GetY();
			
			if($heightakun>$height){
				$pdf->Line($awalxlist, $awalylist+5, $awalxlist, $awalylist+10);
				$pdf->Line($awalxlist+10, $awalylist+5, $awalxlist+10, $awalylist+10);
				$pdf->Line($awalxlist+130, $awalylist+5, $awalxlist+130, $awalylist+10);
				$pdf->Line($awalxlist+160, $awalylist+5, $awalxlist+160, $awalylist+10);
				$pdf->Line($awalxlist+190, $awalylist+5, $awalxlist+190, $awalylist+10);
			}
			
			$pdf->Cell(10,$height,$no,'TRL',0,'C',1);
			$pdf->Cell(20,$height,$dht['noakun'],'TRL',0,'R',1);
			$awalxlistnmakun=$pdf->GetX();
			$pdf->MultiCell(80,$height,$optAkun[$dht['noakun']],'TRL','J');
			$pdf->SetXY($awalxlistnmakun+80, $awalynmakun);
			@$pdf->Cell(20,$height,$dht['noaruskas'],'TRL',0,'C',1);
			@$pdf->Cell(30,$height,number_format($datahdb,2),'TRL',0,'R',1);
			@$pdf->Cell(30,$height,number_format($datahkr,2),'TRL',1,'R',1);
			
			if($heightakun>$height){
				$isi=$pdf->Ln();
			}
			
			########################################
			############## tutup head ##############
			########################################
		}
		
		@$gtotdb=$datahdb+$totdtdb;
        @$gtotkr=$datahkr+$totdtkr;
		
		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(130,$height,'Total',1,0,'C',1);
		$pdf->Cell(30,$height,number_format($gtotdb,2),1,0,'R',1);
		$pdf->Cell(30,$height,number_format($gtotkr,2),1,1,'R',1);


		# Keterangan
		$pdf->MultiCell($width,$height,$_SESSION['lang']['remark'].' : '.$dht['keterangan']);
		# Hutang Unit
		if($dht['hutangunit']==1){
			$pdf->MultiCell($width,$height,'Unit payable Account '.$dht['pemilikhutang'].' : '.$namaakunhutang[$dht['noakunhutang']]);
		}
		$pdf->Ln();


		#################################
		############## ttd ##############
		#################################
		
		$pdf->SetFillColor(220,220,220);
		if($dht['tipetransaksi']=='M'){
			$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
			
			if($tipeLokasiTugas[$dht['kodeorg']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diketahuioleh'],1,0,'C',1);
			}
			
			$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],1,0,'C',1);
			
			if($tipeLokasiTugas[$dht['kodeorg']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dipostingoleh'],1,0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diverifikasioleh'],1,0,'C',1);
			}
			
			$pdf->Ln();
			
			$pdf->SetFillColor(255,255,255);
			for($i=0;$i<4;$i++) {
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
				$pdf->Ln();
			}
			
			// if(isset($userId[$dht['userid']])){
				// $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
			// }else{
				// $pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
			// }
			
			
			
			if($tipeLokasiTugas[$dht['kodeorg']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$karpembuat,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'FA Dept Head','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'Direktur','BLR',0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$karpembuat,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,$karydiset,'BLR',0,'C',1);
			}
			$pdf->Cell(25/100*$width,$height,'Accounting','BLR',0,'C',1);
			$pdf->Ln();

			if($tipeLokasiTugas[$dht['kodeorg']]=='HOLDING'){
				$pdf->Cell(25/100*$width,$height,$tgl,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,$tglap,'BLR',0,'C',1);
			}else{
				$pdf->Cell(25/100*$width,$height,$tgl,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,$tglap,'BLR',0,'C',1);
			}
			$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
			
		}else{
			if($tipeLokasiTugas[$dht['kodeorg']]=='HOLDING'){
				
					$pdf->Cell(23,$height,'',0,0,'C',0);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
					//$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['disetujuioleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['dipostingoleh'],1,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$_SESSION['lang']['diterimaoleh'],1,0,'C',1);
					$pdf->Ln();
					
					$pdf->SetFillColor(255,255,255);
					for($i=0;$i<4;$i++){
						$pdf->Cell(23,$height,'',0,0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						//$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Cell(20/100*$width,$height,'','LR',0,'C',1);
						$pdf->Ln();
					}
					$pdf->Cell(23,$height,'',0,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$karpembuat,'BLR',0,'C',1);
					//$pdf->Cell(20/100*$width,$height,'FA Dept Head','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,$karydiset,'BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,$karpembuat,'BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'','BLR',0,'C',1);
					$pdf->Ln();
					$pdf->Cell(23,$height,'',0,0,'C',1);
					$pdf->Cell(20/100*$width,$height,$tgl,'BLR',0,'C',1);
					//$pdf->Cell(20/100*$width,$height,'FA Dept Head','BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,$tglap,'BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,$tgl1,'BLR',0,'C',1);
					$pdf->Cell(20/100*$width,$height,'','BLR',0,'C',1);
				
			}else{
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dibuatoleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diperiksaoleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['dstujui_oleh'],1,0,'C',1);
				$pdf->Cell(25/100*$width,$height,$_SESSION['lang']['diterimaoleh'],1,0,'C',1);
				$pdf->Ln();
				
				$pdf->SetFillColor(255,255,255);
				for($i=0;$i<4;$i++) {
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Cell(25/100*$width,$height,'','LR',0,'C',1);
					$pdf->Ln();
				}
				
				// if(isset($userId[$dht['userid']])){
					// $pdf->Cell(25/100*$width,$height,$userId[$dht['userid']],'BLR',0,'C',1);
				// }else{
					// $pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				// }
				$pdf->Cell(25/100*$width,$height,$karpembuat,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,$karydiset,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);

				$pdf->Ln();
				$pdf->Cell(25/100*$width,$height,$tgl,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,$tglap,'BLR',0,'C',1);
				$pdf->Cell(25/100*$width,$height,'','BLR',0,'C',1);
			}
		}
		# Print Out
		if($urlefil=='0'){
			$pdf->Output();
		}else{
			$pdf->Output($urlefil);
		}
        break;
		
	case 'excel':
        break;
    case'html':
		$path   = "fileupload/keu_kasbankx/";
		$pathap   = "fileupload/keu_tagihan/";
		$pathaplama   = "filegis/";
		
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
        $tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
        $tab.="<fieldset><legend>".$title."</legend>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
        $tab.="<tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td> :</td><td>".$resH[0]['kodeorg']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td><td> :</td><td>".$param['notransaksi']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['novoucher']."</td><td> :</td><td>".$resH[0]['novoucher']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['cgttu']."</td><td> :</td><td> ".$resH[0]['cgttu']."</td></tr>";
        $tab.="<tr><td>".$_SESSION['lang']['terbilang']."</td><td> :</td><td> ".terbilang($resH[0]['jumlah'],2).
            ' rupiah'."</td></tr>";
        if($resH[0]['hutangunit']==1){
            $tab.="<tr><td>".$_SESSION['lang']['hutangunit']."</td><td> :</td><td> ".'Unit payable Account '.$resH[0]['pemilikhutang'].' : '.$namaakunhutang[$resH[0]['noakunhutang']]."</td></tr>";            
        }
        $tab.="</tbody></table><br />";

            $tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><thead><tr class=rowheader>";



            foreach($colshtml as $column) {
				if($column=='hutangunit1') {
					$tab.="<td align=center>".$_SESSION['lang']['hutangunit']."</td>";
				} else if($column=='pemilikhutang1') {
					$tab.="<td align=center>".$_SESSION['lang']['pemilikhutang']."</td>";
				} else {
					$tab.="<td align=center>".$_SESSION['lang'][$column]."</td>";
				}
            }
            $tab.="</tr></thead><tbody class=rowcontent>";

// echo"<pre>";
// print_r($data);
// echo"</pre>";
        // nyusun ulang nomor setelah disort by debet. dz
            $nyomor=0;
            foreach($data as $key=>$row) {    
                $nyomor+=1;
                $tab.="<tr>";
                foreach($row as $key=>$cont) {
                    if($key=='nomor'){
                        $tab.="<td align=center>".$nyomor."</td>";
                    } else{
                        if($key=='debet' or $key=='kredit') {
                            $tab.="<td align=right>".number_format($cont,2)."</td>";	
                        } else  if ($key=='noaruskas'){
                            $tab.="<td>".$cont."<br>".$nmaruskas[$cont]."</td>";
                        } else  if ($key=='hutangunit1'){
							if($cont==0){
								$tab.="<td align=center>Tidak</td>";
							} else if ($cont==1){
								$tab.="<td align=center>Ya</td>";
							}else{
								$tab.="<td></td>";
							}
                        } else  if ($key=='pemilikhutang1'){
                            $tab.="<td>".$cont."</td>";
                        } else {
							$tab.="<td>".$cont."</td>";
						}							
                    }
                }
                $tab.="</tr>";
            }
        $tab.="<tr><td colspan=4 align=center>Total</td><td align=right>".number_format($totalDebet,2)."</td>
				<td align=right>".number_format($totalKredit,2)."</td>
				<td colspan=3></td></tr>";
             $tab.="</tbody></table> <br /><br />";
		
		
		
		$tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
        $tab.="<thead><tr>
			
				<td>".$_SESSION['lang']['level']."</td>
				<td>".$_SESSION['lang']['karyawanid']."</td>
				<td>".$_SESSION['lang']['status']."</td>
				<td>".$_SESSION['lang']['keterangan']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
			</tr></thead>";
			
		$optposting=array(''=>$_SESSION['lang']['pilihdata'],'0'=>'Belum Diajukan','1'=>'Disetujui','3'=>'Ditolak','9'=>'Proses Persetujuan');
		$str = "select * from ".$dbname.".approval where notransaksi='".$param['notransaksi']."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr class=rowcontent>";
			
				$tab.="<td align=center>".$bar['level']."</td>";
				$tab.="<td>".$nmkaryawan[$bar['karyawanid']]."</td>";
				$tab.="<td>".$optposting[$bar['status']]."</td>";
				$tab.="<td>".$bar['komentar']."</td>";
				$tab.="<td>".tanggalnormal(substr($bar['tanggal'],0,10))." ".substr($bar['tanggal'],11,8)."</td>";
			$tab.="</tr>";
			

		}			
		$tab.="</tbody></table> <br />";	 
		
		
		// $tab.="<table border=0 cellspacing=1 class=sortable hidden>
		// 	<thead>
		// 	<tr style='font-weight:bold'>
		// 		<td align='center'>".$_SESSION['lang']['invoice']."</td>
		// 	</tr>
		// 	</thead>";
		// 	$strinv = "select distinct(keterangan1) as keterangan1 from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
		// 	$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
		// 	$resinv->setFetchMode(PDO::FETCH_ASSOC);
		// 	while($barinv=$resinv->fetch()){
		// 		if($barinv['keterangan1']!=''){
		// 		$tab.="<tr class=rowcontent>";
		// 			$tab.="<td>";
		// 			$_POST['noinvoice']=$barinv['keterangan1'];
		// 			#ambil data header
		// 			$sHeader="select * from ".$dbname.".keu_tagihanht where noinvoice='".$_POST['noinvoice']."'";
		// 			$rHeader=fetchdata($sHeader);
		// 			#ambil data detal
		// 			$sDet="select * from ".$dbname.".keu_tagihandt where noinvoice='".$_POST['noinvoice']."'";
		// 			$rDet=fetchdata($sDet);
		// 			$optSupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$rHeader[0]['kodesupplier']."'");
		// 			$optSupp2=makeOption($dbname,'log_5supkelompok','supplierid,tipe',"supplierid='".$rHeader[0]['kodesupplier']."'");
		// 			$optJur=makeOption($dbname,'keu_5jenistagihan','kode,jurnal');
		// 			$optJenis=makeOption($dbname,'keu_5jenistagihan','kode,namajenis');
		// 			$arrNoyes=array("0"=>"VM","1"=>"NVM");
		// 			$tab.="<table border=0 cellspacing=1 class=sortable>";
		// 			$tab.="<tr class=rowcontent>
		// 						<td>".$_SESSION['lang']['noinvoice']."</td>
		// 						<td>:</td>
		// 						<td>".$_POST['noinvoice']."</td>
								
		// 						<td>".$_SESSION['lang']['nofp']."</td><td>:</td><td>".$rHeader[0]['nofp']."</td>
		// 					</tr>";
		// 			$tab.="<tr class=rowcontent>
		// 						<td>".$_SESSION['lang']['noinvoicesupplier']."</td>
		// 						<td>:</td>
		// 						<td>".$rHeader[0]['noinvoicesupplier']."</td>
		// 						<td>".$_SESSION['lang']['nopo']."</td><td>:</td><td>".$rHeader[0]['nopo']."</td>
		// 					</tr>";
		// 			$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tanggalterima']."</td><td>:</td><td>".tanggalnormal($rHeader[0]['tanggal'])."</td>
		// 				<td>".$_SESSION['lang']['namasupplier']."</td><td>:</td><td>".($optSupp[$rHeader[0]['kodesupplier']]==''?$optSupp2[$rHeader[0]['kodesupplier']]:$optSupp[$rHeader[0]['kodesupplier']])."</td>
		// 			</tr>";
		// 			$nilInvoiceDt=0;
		// 			foreach($rDet as $row=>$lstDt){
		// 				if((substr($lstDt['noakun'],0,3)=="117")||(substr($lstDt['noakun'],0,3)=="213")){
		// 					$nilaipajak+=$lstDt['nilai'];    
		// 				}
		// 			}
				
				  
		// 			$tab.="<tr class=rowcontent>
		// 				<td>".$_SESSION['lang']['nilaiinvoice']."</td>
		// 					<td>:</td>
		// 					<td>".number_format($rHeader[0]['nilaiinvoice'],2)."</td>
		// 					<td>".$_SESSION['lang']['jenis']."</td><td>:</td><td>".$arrNoyes[$optJur[$rHeader[0]['tipeinvoice']]]."-".$optJenis[$rHeader[0]['tipeinvoice']]."</td>
							
		// 				</tr>";
		// 			$tab.="</table>";
		// 				$tab.="<table cellpadding=1 cellspacing=1 border=0 width=100% class=sortable><tbody class=rowcontent>";
		// 				// $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead>";
		// 				$tab.="<tr class=rowheader>";
		// 				$tab.="<td>".$_SESSION['lang']['notransaksi']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['noaruskas']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['noakun']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['nilai']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['kodevhc']."</td>";
		// 				$tab.="<td>".$_SESSION['lang']['adkcip']."</td>";
		// 				$tab.="</tr></thead><tbody>";
		// 				 $totDet=0;
		// 				 $totSma=0;
		// 				foreach($rDet as $row=>$lstDt){
		// 					if($lstDt['nilai']!=0){
		// 						$optNmAkn=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$lstDt['noakun']."'");
		// 						$tab.="<tr class=rowcontent>
		// 						<td>".$lstDt['notransaksi']."</td>
		// 						<td>".$lstDt['noaruskas']."</td>
		// 						<td>".$lstDt['noakun']."</td>";
		// 						$tab.="<td>".$optNmAkn[$lstDt['noakun']]."</td>";
		// 						$tab.="<td align=right>".number_format($lstDt['nilai'],2)."</td>";
		// 						$tab.="<td>".$lstDt['kodevhc']."</td>";
		// 						$tab.="<td>".$lstDt['kodeasset']."</td></tr>";
		// 						@$totalinvoice+=$lstDt['nilai'];
		// 					}
		// 				}
		// 				$tab.="<tr class=rowcontent><td colspan=4>".$_SESSION['lang']['total']." ".$_SESSION['lang']['detail']."</td>";
		// 				$tab.="<td align=right>".number_format($totalinvoice,2)."</td>";
		// 				$tab.="<td colspan=2>&nbsp;</td></tr>";   
		// 				$tab.="</tbody></table>";
		// 			$tab.="</td>";
		// 		$tab.="</tr><br>";
		// 		}
		// 	}
		// $tab.="</table><br>";
		
		$tab.="<table border=0 cellspacing=1 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td align='center'>No.</td>
				<td align='center'>Invoice</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			<tbody id='listfilesview'>";
			
			$strinv = "select distinct(keterangan1) as keterangan1 from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."'";
			$resinv = $owlPDO->query($strinv) or die(print " Gagal: " . PDOException::getMessage());
			$resinv->setFetchMode(PDO::FETCH_ASSOC);
			while($barinv=$resinv->fetch()){
				
			$str="select * from ".$dbname.".listfileupload where notransaksi='".$barinv['keterangan1']."'";
			$res=fetchdata($str);
				foreach($res as $key=>$val){
					$no++;
					$tab.="<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>".$no."</td>
						<td style='text-align:center'>".$barinv['keterangan1']."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.png')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}
					elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}
					elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}
					else
					{
						$tab.="<td style='text-align:center'>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
					
					$tab.="<td style='text-align:left'>".getcriterianame($val['kriteriaefil'])."</td>
						<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='".$pathap.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
							<a href='".$pathaplama.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>
							&nbsp";
					$tab."	</td>
					</tr>";
				}	
			}
			$sFileKas="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."'";
			$rFileKas=fetchdata($sFileKas);
			if(count($rFileKas)!=0){
				foreach($rFileKas as $key=>$val){
					$no++;
					$tab.="<tr id='ppDetailTable' class=rowcontent>
						<td style='text-align:center'>".$no."</td>
						<td style='text-align:center'>".$param['notransaksi']."</td>";
						
					if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.png')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
						</td>";
					}
					elseif($val['formaticon']=='.pdf')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
						</td>";
					}
					elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
						</td>";
					}
					elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx')
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
						</td>";
					}
					else
					{
						$tab.="<td style='text-align:center'>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
						</td>";
					}
					
					$tab.="<td style='text-align:left'>".getcriterianame($val['detail'])."</td>
						<td style='text-align:left'>".$val['namafile']."</td>
						<td align=center>
							<a href='".$path.str_replace('/','',$val['namafile'])."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
					$tab."	</td>
					</tr>";
				}	
			}
		$tab.="</tbody>
		</table>";
			 
			 
		if($param['tampilan']=='PDF'){
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();

			$filepdf=$param['namafile'];
			if (file_exists($filepdf)){
				unlink($filepdf);
			}
			file_put_contents($filepdf, $dompdf->output());
		}else{			
			echo $tab;
		}

    break;
    default:
    break;
}
?>