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
$nokontrak = checkPostGet('nokontrak','');
$kodept = checkPostGet('kodept','');
$tanggalkontrak                 = tanggalsystemn(checkPostGet('tanggalkontrak',''));
$kodecustomer = checkPostGet('kodecustomer','');
$kodebarang = checkPostGet('kodebarang','');
$nospk = checkPostGet('nospk','');
$jenis = checkPostGet('jenis','');
$tanggal                 = tanggalsystemn(checkPostGet('tanggal',''));

$surveyor = checkPostGet('surveyor','');
$kuantitas = checkPostGet('kuantitas','');
$ruanglingkup = checkPostGet('ruanglingkup','');
$tarif = checkPostGet('tarif','');
$pembayaran = checkPostGet('pembayaran','');
$tanggungjawab = checkPostGet('tanggungjawab','');
$pengalihan = checkPostGet('pengalihan','');
$namakapal = checkPostGet('namakapal','');
$pelabuhantujuan = checkPostGet('pelabuhantujuan','');
$nobl = checkPostGet('nobl','');
$jadwaltibakapal                 = tanggalsystemn(checkPostGet('jadwaltibakapal',''));
$alamattibamuatan = checkPostGet('alamattibamuatan','');
$tandatangan = checkPostGet('tandatangan','');
$tandatangan2 = checkPostGet('tandatangan2','');
$jabatan2 = checkPostGet('jabatan2','');
$rupiah = checkPostGet('rupiah','');
$kota = checkPostGet('kota','');
$namaponton = checkPostGet('namaponton','');
$transportir = checkPostGet('transportir','');


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
			$surveyor=$bar['surveyor'];
			$kuantitas=$bar['kuantitas'];
			$ruanglingkup=$bar['ruanglingkup'];
			$tarif=$bar['tarif'];
			$pembayaran=$bar['pembayaran'];
			$tanggungjawab=$bar['tanggungjawab'];
			$pengalihan=$bar['pengalihan'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			$pelabuhantujuan=$bar['pelabuhantujuan'];
			$nobl=$bar['nobl'];
			$jadwaltibakapal=$bar['jadwaltibakapal'];
			$alamattibamuatan=$bar['alamattibamuatan'];
			$tandatangan=$bar['tandatangan'];
			$tandatangan2=$bar['tandatangan2'];
			$jabatan2=$bar['jabatan2'];
			$rupiah=$bar['rupiah'];
			$kota=$bar['kota'];
			$transportir=$bar['transportir'];



		


		$str = "select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='HOLDING'";
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
			$kotacustomer=$bar['kota'];	


		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$surveyor."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namasurveyor=$bar['namasupplier'];			

		$str = "select * from ".$dbname.".log_5supalamat where supplierid='".$surveyor."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatsurveyor=$bar['alamat'];	
			$faxsurveyor=$bar['fax'];		
			$telpsurveyor=$bar['telepon'];			
			$kpsurveyor=$bar['kontakperson'];	


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
		$tab.="<br>";
		
		$cellpadding=7;
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
		
		
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:150px' valign=top><b>Pemberi Jasa</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".$namasurveyor."<br>".$alamatsurveyor."<br>".$telpsurveyor."</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>Ruang Lingkup Jasa</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".nl2br($ruanglingkup)."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>Jenis Produk Yang Disurvey</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'  valign=top>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>Tarif Survey</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".nl2br($tarif)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>".$_SESSION['lang']['pembayaran']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".nl2br($pembayaran)."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>".$_SESSION['lang']['tanggungjawab']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".nl2br($tanggungjawab)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>".$_SESSION['lang']['pengalihan']." ".$_SESSION['lang']['kontrak']." / ".$_SESSION['lang']['spk']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".nl2br($pengalihan)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='width:10px' align=center valign=top><b>".$no.".</td>"; 
				$tab.="<td style='width:100px' valign=top><b>Data Pengapalan</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>";
						$tab.="<table>";
							$tab.="<tr>";
								$nodet=1;
								$tab.="<td style='width:20px' align=left>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['namakapal']."</td>";
								$tab.="<td style='width:10px' align=center>:</td>";
								$tab.="<td style='width:250px'>".$namakapal."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['pelabuhantujuan']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$pelabuhantujuan."</td>";
							$tab.="</tr>";
							$tab.="<tr valign=top>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['bast']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$nobl."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['jadwaltibakapal']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".tglnmblnhr($jadwaltibakapal,'i','long')."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['jenisbarang']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$nmkomoditi[$kodebarang]."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['NoKontrak']." & ".$_SESSION['lang']['kuantitas']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$nokontrak." = ".number_format($kuantitas)." Kg</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$nodet++;
								$tab.="<td>".$no.".".$nodet."</td>";
								$tab.="<td>".$_SESSION['lang']['penerimaan']." ".$_SESSION['lang']['barang']."</td>";
								// $tab.="<td>:</td>";
								// $tab.="<td>".$nokontrak." = ".number_format($kuantitas)." Kg</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td></td>";
								$tab.="<td>".$_SESSION['lang']['pt']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$nmcustsomer[$kodecustomer]."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td></td>";
								$tab.="<td>".$_SESSION['lang']['alamat']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$alamatcustomer."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td></td>";
								$tab.="<td>".$_SESSION['lang']['kota']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$kotacustomer."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td></td>";
								$tab.="<td>".$_SESSION['lang']['telp']."/".$_SESSION['lang']['fax']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$telpcustomer." / ".$faxcustomer."</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td></td>";
								$tab.="<td>".$_SESSION['lang']['alamattibamuatan']."</td>";
								$tab.="<td align=center>:</td>";
								$tab.="<td>".$alamattibamuatan."</td>";
							$tab.="</tr>";
							
							
							#= nanti diedit
							$nocp=1;
							$str = "select * from ".$dbname.".pmn_4customercontact where kodecustomer='".$kodecustomer."'";
							$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()){
								
								if($nocp==1){
									$tab.="<tr>";
										$tab.="<td></td>";
										$tab.="<td>".$_SESSION['lang']['cperson']."</td>";
										$tab.="<td align=center>:</td>";
										$tab.="<td>".$bar['nama']." / ".$bar['telepon']."</td>";
									$tab.="</tr>";
								}else{
									$tab.="<tr>";
										$tab.="<td></td>";
										$tab.="<td></td>";
										$tab.="<td align=center></td>";
										$tab.="<td>".$bar['nama']." / ".$bar['telepon']."</td>";
									$tab.="</tr>";
								}
								$nocp++;
							}
							
							
							
						$tab.="</table>";			   
					$tab.="</td>"; 
			$tab.="</tr>";
			
		$tab.="</table>";	
	
		$tab.="<br>";
	
		$cellpadding=2;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
		$tab.="<tr>";
			$tab.="<td>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
		$tab.="</tr>";
			
		$cellpadding=0.5;	
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:270px' align=center>Penerima Jasa</td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:270px' align=center>Pemberi Jasa</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:270px' align=center>".$namapt."</td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:270px' align=center>".$namacustomer."</td>"; 
			$tab.="</tr>";
		
			for($i=1;$i<5;$i++){
				$tab.="<tr>";
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
			$tab.="</tr>";
			}
		
			$tab.="<tr>";
				$tab.="<td style='width:270px;border-bottom:0.5px solid #000000' align=center><b>".$nmkaryawan[$tandatangan]."</b></td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:270px;border-bottom:0.5px solid #000000' align=center><b>".$tandatangan2."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:270px' align=center>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
				$tab.="<td style='width:200px' align=center></td>"; 
				$tab.="<td style='width:270px' align=center>".$jabatan2."</td>"; 
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
		$nospk = generatenospk();		
	
		$data = array(
			'nokontrak'=>$nokontrak,
			'kodept'=>$kodept,
			'tanggalkontrak'=>$tanggalkontrak,
			'kodecustomer'=>$kodecustomer,
			'kodebarang'=>$kodebarang,
			'nospk'=>$nospk,
			'jenis'=>$jenis,
			'tanggal'=>$tanggal,
			'surveyor'=>$surveyor,
			'kuantitas'=>$kuantitas,
			'ruanglingkup'=>$ruanglingkup,
			'tarif'=>$tarif,
			'pembayaran'=>$pembayaran,
			'tanggungjawab'=>$tanggungjawab,
			'pengalihan'=>$pengalihan,
			'transportir'=>$transportir,
			'namakapal'=>$namakapal,
			'namaponton'=>$namaponton,
			'pelabuhantujuan'=>$pelabuhantujuan,
			'nobl'=>$nobl,
			'jadwaltibakapal'=>$jadwaltibakapal,
			'alamattibamuatan'=>$alamattibamuatan,
			'tandatangan'=>$tandatangan,
			'tandatangan2'=>$tandatangan2,
			'jabatan2'=>$jabatan2,
			'rupiah'=>$rupiah,
			'kota'=>$kota,
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
				surveyor='".$surveyor."',
				kuantitas='".$kuantitas."',
				ruanglingkup='".$ruanglingkup."',
				tarif='".$tarif."',
				pembayaran='".$pembayaran."',
				tanggungjawab='".$tanggungjawab."',
				pengalihan='".$pengalihan."',
				transportir='".$transportir."',
				namakapal='".$namakapal."',
				namaponton='".$namaponton."',
				pelabuhantujuan='".$pelabuhantujuan."',
				nobl='".$nobl."',
				jadwaltibakapal='".$jadwaltibakapal."',
				alamattibamuatan='".$alamattibamuatan."',
				tandatangan='".$tandatangan."',
				jabatan2='".$jabatan2."',
				tandatangan2='".$tandatangan2."',
				rupiah='".$rupiah."',
				kota='".$kota."'
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
				<th align=center>".$_SESSION['lang']['nospk']."<br>".$_SESSION['lang']['jenis']."<br>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['surveyor']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['ruanglingkup']."</th>
				<th align=center>".$_SESSION['lang']['tarif']."</th>
				<th align=center>".$_SESSION['lang']['pembayaran']."</th>
				<th align=center>".$_SESSION['lang']['tanggungjawab']."</th>
				<th align=center>".$_SESSION['lang']['pengalihan']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."<br><br>".$_SESSION['lang']['namakapal']."<br><br>".$_SESSION['lang']['namaponton']."</th>
				<th align=center>".$_SESSION['lang']['pelabuhantujuan']."</th>
				<th align=center>".$_SESSION['lang']['bast']."</th>
				<th align=center>".$_SESSION['lang']['jadwaltibakapal']."</th>
				<th align=center>".$_SESSION['lang']['alamattibamuatan']."</th>
				<th align=center>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>".$_SESSION['lang']['tandatangan']."<br>Pemberi Jasa</th>
				<th align=center>".$_SESSION['lang']['kota']."</th>
				<th align=center  colspan=5>".$_SESSION['lang']['action']."</th>
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
				$tab.="<td valign=top>".$bar['nospk']."<br><br>".$namajenis[$bar['jenis']]."<br><br>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".$nmsurveyor[$bar['surveyor']]."</td>";
				$tab.="<td valign=top>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td valign=top>".$bar['ruanglingkup']."</td>";
				$tab.="<td valign=top>".$bar['tarif']."</td>";
				$tab.="<td valign=top>".$bar['pembayaran']."</td>";
				$tab.="<td valign=top>".$bar['tanggungjawab']."</td>";
				$tab.="<td valign=top>".$bar['pengalihan']."</td>";
				$tab.="<td valign=top>".$nmtransportir[$bar['transportir']]."<br><br>".$nmkapalponton[$bar['namakapal']]."<br><br>".$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td valign=top>".$nmfranco[$bar['pelabuhantujuan']]."</td>";
				$tab.="<td valign=top>".$bar['nobl']."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['jadwaltibakapal'])."</td>";
				$tab.="<td valign=top>".$bar['alamattibamuatan']."</td>";
				$tab.="<td valign=top>".$nmkaryawan[$bar['tandatangan']]."</td>";
				$tab.="<td valign=top>".$bar['tandatangan2']."<br><br>".$bar['jabatan2']."</td>";
				$tab.="<td valign=top>".$bar['kota']."</td>";
		
				// $tab.="<td align=center  valign=top>";
				if($bar['posting']==0){
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar['nokontrak']."','".$bar['jenis']."');\"></td>";
				
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
	
		$str = "select * from ".$dbname.".".$table."  where nokontrak='".$nokontrak."' and jenis='".$jenis."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			

			
		echo $bar['kodept']."###".tanggalnormal($bar['tanggalkontrak'])."###".$bar['kodecustomer']."###".$bar['kodebarang']."###".$bar['nospk']
		."###".$bar['jenis']."###".tanggalnormal($bar['tanggal'])."###".number_format($bar['kuantitas'])."###".$bar['ruanglingkup']."###".$bar['tarif']
		."###".$bar['pembayaran']."###".$bar['tanggungjawab']."###".$bar['pengalihan']."###".$bar['namakapal']."###".$bar['pelabuhantujuan']
		."###".$bar['nobl']."###".tanggalnormal($bar['jadwaltibakapal'])."###".$bar['alamattibamuatan']."###".$bar['tandatangan']."###".$bar['rupiah']
		."###".$bar['kota']."###".$bar['surveyor']."###".$bar['tandatangan2']."###".$bar['jabatan2']
		."###".$bar['transportir']."###".$bar['namaponton'];
	break;
    default:
	break;
}
?>
