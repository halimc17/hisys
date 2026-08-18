<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$path=$_SERVER['REQUEST_URI'];
$path=explode('/',$path);
$rowfile=count($path);
$file=$path[($rowfile-1)];
$file=explode('?',$file);
$file=$file[0];	

$method                         = checkPostGet('method','');

$nokontrak                      = checkPostGet('nokontrak','');
$kodept                         = checkPostGet('kodept','');
$tanggalkontrak                 = tanggalsystemn(checkPostGet('tanggalkontrak',''));
$kodecustomer                   = checkPostGet('kodecustomer','');
$kodebarang                     = checkPostGet('kodebarang','');
$nospk                          = checkPostGet('nospk','');
$jenis                          = checkPostGet('jenis','');
$tanggal                        = tanggalsystemn(checkPostGet('tanggal',''));
$tanggalkedatangan              = tanggalsystemn(checkPostGet('tanggalkedatangan',''));
$transportir                    = checkPostGet('transportir','');
$kuantitas                      = checkPostGet('kuantitas','');
$kuantitaskemasan               = checkPostGet('kuantitaskemasan','');
$pelabuhanmuat                  = checkPostGet('pelabuhanmuat','');
$pelabuhanbongkar               = checkPostGet('pelabuhanbongkar','');
$tandatangan                    = checkPostGet('tandatangan','');
$surveyor                       = checkPostGet('surveyor','');
$rupiah                         = checkPostGet('rupiah','');
$asalkargo                      = checkPostGet('asalkargo','');


$namakapal                      = checkPostGet('namakapal','');
$namaponton                      = checkPostGet('namaponton','');
$pelabuhanmuat                  = checkPostGet('pelabuhanmuat','');
$pelabuhantujuan                = checkPostGet('pelabuhantujuan','');
$tanggalsurvey1                 = tanggalsystemn(checkPostGet('tanggalsurvey1',''));
$tanggalsurvey2                 = tanggalsystemn(checkPostGet('tanggalsurvey2',''));
$kota                           = checkPostGet('kota','');
$rupiah                         = checkPostGet('rupiah','');

$tandatangan1                   = checkPostGet('tandatangan1','');
$tandatangan2                   = checkPostGet('tandatangan2','');
$jenispekerjaan                 = checkPostGet('jenispekerjaan','');
$fee                            = checkPostGet('fee','');
$note                           = checkPostGet('note','');

// $jenispekerjaan=replaceEnter($jenispekerjaan,'####');
// $fee=replaceEnter($fee,'####');
// $note=replaceEnter($note,'####');


#'".str_replace('####','\n',$bar['spesifikasi'])."'

$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');
$nmjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmtransportir[$bar['supplierid']]=$bar['namasupplier'];
}

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('JASAANALISA') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsurveyor[$bar['supplierid']]=$bar['namasupplier'];
}

$str = "select * from ".$dbname.".datakaryawan where tanggalkeluar='0000-00-00' and tipekaryawan in ('0','7','8','9') order by namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmkaryawan[$bar['karyawanid']]=$bar['namakaryawan'];
	$kodejabatan[$bar['karyawanid']]=$bar['kodejabatan'];
}


$str="select * from ".$dbname.".pmn_5jenisspk where kode='".$jenis."'";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
	$table=$bar['file'];
	$namajenis[$jenis]=$bar['nama'];
	

switch ($method) {
	
	
	case'printpdf':
		
	
		$str = "select * from ".$dbname.".".$table." where nokontrak='".$nokontrak."' and nospk='".$nospk."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$nokontrak=$bar['nokontrak'];
			$kodept=$bar['kodept'];
			$tanggalkontrak=$bar['tanggalkontrak'];
			$kodecustomer=$bar['kodecustomer'];
			$kodebarang=$bar['kodebarang'];
			$nospk=$bar['nospk'];
			$jenis=$bar['jenis'];
			$tanggal=$bar['tanggal'];
			$transportir=$bar['transportir'];
			// $kuantitas=$bar['kuantitas'];
			$pelabuhanmuat=$nmfranco[$bar['pelabuhanmuat']];
			$pelabuhantujuan=$nmfranco[$bar['pelabuhantujuan']];
			$surveyor=$bar['surveyor'];
			$tanggalsurvey1=$bar['tanggalsurvey1'];
			$tanggalsurvey2=$bar['tanggalsurvey2'];
			$tanggalkedatangan=$bar['tanggalkedatangan'];
			$jenispekerjaan=$bar['jenispekerjaan'];
			$fee=$bar['fee'];
			$note=$bar['note'];
			$tandatangan=$bar['tandatangan'];
			$tandatangan2=$bar['tandatangan2'];
			$kota=$bar['kota'];
			$rupiah=$bar['rupiah'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];

		#= nokontrak dipindah
		$listnokontrak="";
		$str = "select * from ".$dbname.".".$table." where  nospk='".$nospk."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			// $listnokontrak.=$bar['nokontrak']."<t>&nbsp;&nbsp;&nbsp; +/- ".number_format($bar['kuantitas'])." Kg   <br>";
			$listnokontrak.="<table cellpadding=1 cellspacing=1 border=0 style=width:100%>";
			$listnokontrak.="<tr>";
				$listnokontrak.="<td style=width:50%>".$bar['nokontrak']."</td>";
				$listnokontrak.="<td  style=width:10% align=center>+/-</td>";
				$listnokontrak.="<td align=center>".number_format($bar['kuantitas'])." Kg</td>";
			$listnokontrak.="</tr>";
			$listnokontrak.="</table>";
			@$kuantitas+=$bar['kuantitas'];
		}

		$str = "select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='KANWIL'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kotaro=$bar['wilayahkota'];
			$alamatro=$bar['alamat'];
			$telpro=$bar['telepon'];
			$faxro=$bar['fax'];
			
		$str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatpt=$bar['alamat'];	
			$namapt=$bar['namaorganisasi'];	
			$telppt=$bar['telepon'];
			$faxpt=$bar['fax'];			
			
		$str = "select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatcustomer=$bar['alamat'];	
			$namacustomer=$bar['namacustomer'];		
			$telpcustomer=$bar['telepon'];	
			$faxcustomer=$bar['fax'];	

		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$transportir."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namatransportir=$bar['namasupplier'];		
			
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$surveyor."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namasurveyor=$bar['namasupplier'];			

			
			
		$str = "select * from ".$dbname.".log_5supalamat where supplierid='".$transportir."' and status=1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamattransportir=$bar['alamat'];	
			$faxtransportir=$bar['fax'];		
			$telptransportir=$bar['telepon'];			
			$kptransportir=$bar['kontakperson'];	


		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$bongkarmuat."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namabongkarmuat=$bar['namasupplier'];				
			
		$str = "select * from ".$dbname.".log_5supalamat where supplierid='".$bongkarmuat."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatbongkarmuat=$bar['alamat'];	
			$faxbongkarmuat=$bar['fax'];		
			$telpbongkarmuat=$bar['telepon'];			
			$kpbongkarmuat=$bar['kontakperson'];		
	
		$tab="<style>
				@page {
					margin-top: 50px;
					margin-left: 50px;
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
			
		
			/*
			background-color: #03a9f4;
					color: white;
					text-align: center;
					line-height: 35px;
			*/
		$cellpadding=1.5;
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:24px'><b><u>".$namajenis[$jenis]."</u></b></td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:16px'>".$nospk."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
		
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Dengan ini ".$namapt." menunjuk ".$namasurveyor." untuk melaksanakan 
					tugas loading Survey dan Sampling sebagai berikut :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<br>";
		$cellpadding=7;
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
		
		
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;width:30px' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jenis Komoditi</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;width:400px'>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kuantitas</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/-&nbsp;&nbsp; ".number_format($kuantitas)." Kg</td>"; 
			$tab.="</tr>";
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jenis Angkutan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$kapalponton."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Agen Pelayaran</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$namatransportir."</td>"; 
			$tab.="</tr>";
			
			
		// echo $tab;
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' valign=top>Jadwal kedatangan Kapal (Pembeli)<br>Hari / Tanggal</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>:</td>"; 
				$tab.="<td style='border:1px solid #000000;' valign=top>".tglnmblnhr($tanggalkedatangan,'i','long')."</td>"; 
			$tab.="</tr>";
			
			
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tempat Pemuatan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$pelabuhanmuat."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Pelabuhan Tujuan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$pelabuhantujuan."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td valign=top style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td valign=top  style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['NoKontrak']."</td>"; 
				$tab.="<td valign=top  style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td valign=top  style='border:1px solid #000000;'>".$listnokontrak."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['customer']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmcustsomer[$kodecustomer]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' valign=top>Jenis Pekerjaan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>:</td>"; 
				$tab.="<td style='border:1px solid #000000;' valign=top>".nl2br($jenispekerjaan)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' valign=top>Jadwal Kegiatan Survey</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>:</td>"; 
				$tab.="<td style='border:1px solid #000000;' valign=top>".tglnmblnhr($tanggalsurvey1,'i','long')." s/d ".tglnmblnhr($tanggalsurvey2,'i','long')."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' valign=top>Fee</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>:</td>"; 
				$tab.="<td style='border:1px solid #000000;' valign=top>".nl2br($fee)."</td>"; 
			$tab.="</tr>";
						
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' valign=top>Note</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center valign=top>:</td>"; 
				$tab.="<td style='border:1px solid #000000;' valign=top>".nl2br($note)."</td>"; 
			$tab.="</tr>";

		$tab.="</table>";	
		$tab.="<br>";
	
	
		$cellpadding=3;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
			$tab.="</tr>";

		for($i=1;$i<2;$i++){
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			}
		$tab.="</table>";	
			
		$cellpadding=0.5;	
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>Hormat Kami,</td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:200px' align=center>Mengetahui</td>"; 
			$tab.="</tr>";
		
			for($i=1;$i<5;$i++){
				$tab.="<tr>";
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
			$tab.="</tr>";
			}
			
			
		
			$tab.="<tr>";
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan]."</td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				if($nmkaryawan[$tandatangan2]!=''){
						$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan2]."</td>"; 
				}
			
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
				$tab.="<td style='width:200px' align=center></td>"; 
				if($nmjabatan[$kodejabatan[$tandatangan2]]!=''){
					$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan2]]."</td>"; 
				}
				
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td >CC : - MM PMKS</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - File</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		

		
		$tab.="<footer>";
			$cellpadding=1;	
			$tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
				$tab.="<tr>";
					$tab.="<td align=left style='width:700px;border-bottom:0.5px solid #000000'><b>".$namapt."</b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=left><b>".$kotaro." Office</b> : ".$alamatro." Tel : ".$telpro." Fax : ".$faxro."</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
		$tab.="</footer>";	
		
			$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	
	break;
	
	
	case'posting':
		$str = "update ".$dbname.".".$table." set 
			posting='1' where nospk='".$nospk."'";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	case'delete':
		$str = "delete from ".$dbname.".".$table." where nospk='".$nospk."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
		
	case 'insert':
		
		#generet nokontrak
		if($nospk==''){	
			$nospk = generatenospk();	
		}
		$data = array(
			'nokontrak' => $nokontrak,
			'kodept' => $kodept,
			'tanggalkontrak' => $tanggalkontrak,
			'kodecustomer' => $kodecustomer,
			'kodebarang' => $kodebarang,
			'nospk' => $nospk,
			'jenis' => $jenis,
			'tanggal' => $tanggal,
			'transportir' => $transportir,
			'kuantitas' => $kuantitas,
			'pelabuhanmuat' => $pelabuhanmuat,
			'pelabuhantujuan' => $pelabuhantujuan,
			'tanggalsurvey1' => $tanggalsurvey1,
			'tanggalsurvey2' => $tanggalsurvey2,
			'tanggalkedatangan' => $tanggalkedatangan,
			'namakapal' => $namakapal,
			'namaponton' => $namaponton,
			'jenispekerjaan' => $jenispekerjaan,
			'fee' => $fee,
			'note' => $note,
			'tandatangan' => $tandatangan1,
			'tandatangan2' => $tandatangan2,
			'kota' => $kota,
			'rupiah' => $rupiah,
			'surveyor' => $surveyor,
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d H:i'),
			'updateby' => $_SESSION['standard']['userid']
		);
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); #exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}

	break;
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
				tanggal='".$tanggal."',
				transportir='".$transportir."',
				kuantitas='".$kuantitas."',
				pelabuhanmuat='".$pelabuhanmuat."',
				pelabuhantujuan='".$pelabuhantujuan."',
				tanggalsurvey1='".$tanggalsurvey1."',
				tanggalsurvey2='".$tanggalsurvey2."',
				tanggalkedatangan='".$tanggalkedatangan."',
				namakapal='".$namakapal."',
				namaponton='".$namaponton."',
				jenispekerjaan='".$jenispekerjaan."',
				fee='".$fee."',
				note='".$note."',
				tandatangan='".$tandatangan1."',
				tandatangan2='".$tandatangan2."',
				kota='".$kota."',
				surveyor='".$surveyor."',
				rupiah='".$rupiah."'
			where nospk = '".$nospk."' and nokontrak='".$nokontrak."'";#exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;

   case'loaddata':
	
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['NoKontrak']." <br>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['tglKontrak']." <br>".$_SESSION['lang']['Pembeli']."</th>
				<th align=center>".$_SESSION['lang']['komoditi']."</th>
				<th align=center>".$_SESSION['lang']['nospk']."<br>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['surveyor']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['namakapal']."<br><br>".$_SESSION['lang']['namaponton']."</th>
				<th align=center>".$_SESSION['lang']['pelabuhanmuat']."<br><br>".$_SESSION['lang']['pelabuhanbongkar']."</th>
				<th align=center>Tgl Kapal Datang<br><br>Jadwal Keg Survey</th>
				<th align=center>".$_SESSION['lang']['kota']."<br><br>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>Jenis Pekerjaan</th>
				<th align=center>Fee</th>
				<th align=center>".$_SESSION['lang']['note']."</th>
				<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
		$no = 0;
		$str = "select * from ".$dbname.".".$table." where nokontrak='".$nokontrak."' and jenis='".$jenis."' ";
		// exit("Error:$str");
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent style=vertical-align:top>";
				$tab.="<td valign=top>".$bar['nokontrak']."<br><br>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalkontrak'])."<br><br>".$nmcustsomer[$bar['kodecustomer']]."</td>";
				$tab.="<td valign=top>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td valign=top>".$bar['nospk']."<br><br>".$namajenis[$bar['jenis']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".$nmsurveyor[$bar['surveyor']]."</td>";
				$tab.="<td valign=top>".$nmtransportir[$bar['transportir']]."</td>";
				$tab.="<td valign=top>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td valign=top>".$nmkapalponton[$bar['namakapal']]."<br><br>".$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td valign=top>".$nmfranco[$bar['pelabuhanmuat']]."<br><br>".$nmfranco[$bar['pelabuhantujuan']]."</td>";
				$tab.="<td valign=top align=center>".tanggalnormal($bar['tanggalkedatangan'])."<br><br>".tanggalnormal($bar['tanggalsurvey1'])."<br>s/d<br>".tanggalnormal($bar['tanggalsurvey2'])."</td>";
	
				$tab.="<td valign=top>".$bar['kota']."<br><br>1. ".$nmkaryawan[$bar['tandatangan']]."<br><br>2. ".$nmkaryawan[$bar['tandatangan2']]."</td>";
				$tab.="<td valign=top>".$bar['jenispekerjaan']."</td>";
				$tab.="<td valign=top>".$bar['fee']."</td>";
				$tab.="<td valign=top>".$bar['note']."</td>";
          
		
		
		
				// $tab.="<td align=center>";
				if($bar['posting']==0){
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar['nospk']."','".$bar['jenis']."');\"></td>";
				
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\"></td>";		
					
					$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";							
				} else{
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' ></td>";
				}
		
			$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$bar['nokontrak']."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";	
				
			$tab.="<td align=center width=25px><img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$bar['nospk']."','SALES_".$bar['jenis']."') title=Upload>";
			
			$tab.="</td>";
            $tab.="</tr>";
        }
		$tab.="</table>";
		
		
		
		
		
		
		
		
		
		
		
		echo $tab;
	break;
	case'getEditData':
	
		$str = "select * from ".$dbname.".".$table."  where nospk='".$nospk."' and jenis='".$jenis."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			
		echo $bar['kodept']."###".tanggalnormal($bar['tanggalkontrak'])."###".$bar['kodecustomer']."###".$bar['kodebarang']."###".$bar['nospk']."###".$bar['jenis']."###".tanggalnormal($bar['tanggal'])."###".$bar['transportir']."###".number_format($bar['kuantitas'])."###".$bar['namakapal']."###".$bar['pelabuhanmuat']."###".$bar['pelabuhantujuan']."###".tanggalnormal($bar['tanggalsurvey1'])."###".tanggalnormal($bar['tanggalsurvey2'])."###".tanggalnormal($bar['tanggalkedatangan'])."###".$bar['kota']."###".$bar['tandatangan']."###".$bar['tandatangan2']."###".$bar['rupiah']."###".$bar['jenispekerjaan']."###".$bar['fee']."###".$bar['note']."###".$bar['surveyor']."###".$bar['namaponton'];
	break;
    default:
	break;
}
?>
