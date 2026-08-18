<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method  = checkPostGet('method','');

$nokontrak = checkPostGet('nokontrak','');
$kodept = checkPostGet('kodept','');
$tanggalkontrak = tanggalsystemn(checkPostGet('tanggalkontrak',''));
$kodecustomer = checkPostGet('kodecustomer','');
$kodebarang = checkPostGet('kodebarang','');
$nospk = checkPostGet('nospk','');
$jenis = checkPostGet('jenis','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));
$tanggalpelaksanaan = tanggalsystemn(checkPostGet('tanggalpelaksanaan',''));
$kuantitas = checkPostGet('kuantitas','');
$rupiah = checkPostGet('rupiah','');
$kota = checkPostGet('kota','');
$surveyor = checkPostGet('surveyor','');
$tandatangan = checkPostGet('tandatangan','');
$tandatangan2 = checkPostGet('tandatangan2','');
$sample = checkPostGet('sample','');
$parameter = checkPostGet('parameter','');
$tempatpelaksanaan = checkPostGet('tempatpelaksanaan','');
$pelabuhantujuan = checkPostGet('pelabuhantujuan','');
$pekerjaan = checkPostGet('pekerjaan','');
$namaponton = checkPostGet('namaponton','');
$namakapal = checkPostGet('namakapal','');
$transportir = checkPostGet('transportir','');
$nosip = checkPostGet('nosip','');




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

$path=$_SERVER['REQUEST_URI'];
$path=explode('/',$path);
$rowfile=count($path);
$file=$path[($rowfile-1)];
$file=explode('?',$file);
$file=$file[0];	

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
			$surveyor=$bar['surveyor'];
			$tanggal=$bar['tanggal'];
			$kuantitas=$bar['kuantitas'];
			$sample=$bar['sample'];
			$tanggalpelaksanaan=$bar['tanggalpelaksanaan'];
			$tempatpelaksanaan=$bar['tempatpelaksanaan'];
			$pelabuhantujuan=$bar['pelabuhantujuan'];
			$pekerjaan=$bar['pekerjaan'];
			$parameter=$bar['parameter'];
			$tandatangan=$bar['tandatangan'];
			$tandatangan2=$bar['tandatangan2'];
			$kota=$bar['kota'];
			$rupiah=$bar['rupiah'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			$nosip=$bar['nosip'];

		


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

		// $str = "select * from ".$dbname.".log_5supplier where supplierid='".$surveyor."'";
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
			// $namatransportir=$bar['namasupplier'];				
			
		// $str = "select * from ".$dbname.".log_5supalamat where supplierid='".$surveyor."'";
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
			// $alamattransportir=$bar['alamat'];	
			// $faxtransportir=$bar['fax'];		
			// $telptransportir=$bar['telepon'];			
			// $kptransportir=$bar['kontakperson'];	


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
		
		// $tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			// $tab.="<tr>";
				// $tab.="<td>Dengan ini ".$namapt." menunjuk ".$nmsurveyor[$surveyor]." untuk
						// melaksanakan tugas Survey dan Analisa Mutu sebagai berikut :
						// </td>"; 
			// $tab.="</tr>";
		// $tab.="</table>";	
		
		$tab.="<br>";
		$cellpadding=10;
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
			$tab.="<tr>";
				$tab.="<td colspan=4>Dengan ini ".$namapt." menunjuk ".$nmsurveyor[$surveyor]." untuk
						melaksanakan tugas Survey dan Analisa Mutu sebagai berikut :
						</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:20px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;width:170px'>Jenis Komoditi</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;width:20px' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;width:375px'>".$nmkomoditi[$kodebarang]." Kg</td>"; 
			$tab.="</tr>";
			
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kapal / Ponton</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$kapalponton."</td>"; 
			$tab.="</tr>";
			
			
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kuantitas</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/-&nbsp;&nbsp; ".number_format($kuantitas)." Kg</td>"; 
			$tab.="</tr>";
			
			if($sample!=''){
				$tab.="<tr>";
					$no++;
					$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center  valign=top>".$no.".</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'  valign=top>".$_SESSION['lang']['sample']."</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center  valign=top>:</td>"; 
					$tab.="<td style='border:1px solid #000000;' valign=top>".nl2br($sample)."</td>"; 
				$tab.="</tr>";
			}
			
			

		
		
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tempat & Jadwal Pelaksanaan<br>Hari / Tanggal</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmfranco[$tempatpelaksanaan]."<br>".tglnmblnhr($tanggalpelaksanaan,'i','long')."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Parameter Yang Diuji</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$parameter."</td>"; 
			$tab.="</tr>";
			
			if($pekerjaan!=''){
				$tab.="<tr>";
					$no++;
					$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center  valign=top>".$no.".</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'  valign=top>".$_SESSION['lang']['pekerjaan']."</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center  valign=top>:</td>"; 
					$tab.="<td style='border:1px solid #000000;' valign=top>".nl2br($pekerjaan)."</td>"; 
				$tab.="</tr>";
			}
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Biaya Analisa Persample</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>Rp.&nbsp;&nbsp;".number_format($rupiah,0)." &nbsp;&nbsp; ditambah PPN</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		$tab.="<br>";
	
	
		
		$cellpadding=3;
		$tab.="<table width=80% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
				if($nosip!=''){
					$tab.="<td>NB : SIP No. ".$nosip."</td>"; 
				}
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
				$tab.="<td style='width:200px' align=center>Mengetahui,</td>"; 
			$tab.="</tr>";
		
			for($i=1;$i<5;$i++){
				$tab.="<tr>";
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
			$tab.="</tr>";
			}
		
			$tab.="<tr>";
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan]."</td>"; 
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan2]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
				$tab.="<td style='width:200px' align=center></td>"; 
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan2]]."</td>"; 
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
		// exit("Error:$str");
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
					'nokontrak' => $nokontrak,
					'kodept' => $kodept,
					'tanggalkontrak' => $tanggalkontrak,
					'kodecustomer' => $kodecustomer,
					'kodebarang' => $kodebarang,
					'nospk' => $nospk,
					'jenis' => $jenis,
					'tanggal' => $tanggal,
					'surveyor' => $surveyor,
					'kuantitas' => $kuantitas,
					'tanggalpelaksanaan' => $tanggalpelaksanaan,
					'sample' => $sample,
					'parameter' => $parameter,
					'tandatangan' => $tandatangan,
					'tandatangan2' => $tandatangan2,
					'kota' => $kota,
					'rupiah' => $rupiah,
					'tempatpelaksanaan' => $tempatpelaksanaan,
					'pelabuhantujuan' => $pelabuhantujuan,
					'transportir' => $transportir,
					'pekerjaan' => $pekerjaan,
					'namaponton' => $namaponton,
					'namakapal' => $namakapal,
					'nosip' => $nosip,
					'createby' => $_SESSION['standard']['userid'],
					'createtime' => date('Y-m-d H:i'),
					'updateby' => $_SESSION['standard']['userid']
					);
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 
		// exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}

	break;
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
				tanggal='".$tanggal."',
				surveyor='".$surveyor."',
				kuantitas='".$kuantitas."',
				tanggalpelaksanaan='".$tanggalpelaksanaan."',
				sample='".$sample."',
				parameter='".$parameter."',
				tandatangan='".$tandatangan."',
				tandatangan2='".$tandatangan2."',
				kota='".$kota."',
				tempatpelaksanaan='".$tempatpelaksanaan."',
				pelabuhantujuan='".$pelabuhantujuan."',
				pekerjaan='".$pekerjaan."',
				nosip='".$nosip."',
				transportir='".$transportir."',
				namakapal='".$namakapal."',
				namaponton='".$namaponton."',
				rupiah='".$rupiah."'
			where nospk = '".$nospk."' and nokontrak='".$nokontrak."'";
			// exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;

   case'loaddata':
	
		$tab="<table class=sortable cellpadding=1 cellspacing=1 border=0 width=100%>
			<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['NoKontrak']."<br><br>".$_SESSION['lang']['kodept']."</td>
				<td align=center>".$_SESSION['lang']['tglKontrak']."<br><br>".$_SESSION['lang']['Pembeli']."</td>
				<td align=center>".$_SESSION['lang']['komoditi']."</td>
				<td align=center>".$_SESSION['lang']['nospk']."<br><br>".$_SESSION['lang']['jenis']."<br><br>".$_SESSION['lang']['tanggal']."</td>
			
				<td align=center>".$_SESSION['lang']['surveyor']."</td>
				<td align=center>No. SIP</td>
				<td align=center>".$_SESSION['lang']['transportir']."<br><br>".$_SESSION['lang']['namakapal']."<br><br>".$_SESSION['lang']['namaponton']."</td>
				<td align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</td>
				<td align=center>Tempat<br>".$_SESSION['lang']['tanggal']."<br>Pelaksanaan</td>
				<td align=center>".$_SESSION['lang']['pelabuhantujuan']."</td>
				<td align=center>Kota<br>(dibuat di)<br><br>".$_SESSION['lang']['tandatangan']."</td>
				<td align=center>".$_SESSION['lang']['biaya']."</td>
				<td align=center>".$_SESSION['lang']['sample']."</td>
				<td align=center>".$_SESSION['lang']['pekerjaan']."</td>
				<td align=center style=\"width:60px;\">".$_SESSION['lang']['action']."</td>
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
			$tab.="<tr class=rowcontent>";
				$tab.="<td valign=top>".$bar['nokontrak']."<br><br>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalkontrak'])."<br><br>".$nmcustsomer[$bar['kodecustomer']]."</td>";
				$tab.="<td valign=top>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td valign=top>".$bar['nospk']."<br><br>".$namajenis[$bar['jenis']]."<br><br>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".$nmsurveyor[$bar['surveyor']]."</td>";
				$tab.="<td valign=top>".$bar['nosip']."</td>";
				$tab.="<td valign=top>".$nmtransportir[$bar['transportir']]."<br><br>".$nmkapalponton[$bar['namakapal']]."<br><br>".$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td valign=top>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td valign=top>".$nmfranco[$bar['tempatpelaksanaan']]."<br><br>".tanggalnormal($bar['tanggalpelaksanaan'])."</td>";
				$tab.="<td valign=top>".$nmfranco[$bar['pelabuhantujuan']]."</td>";
				$tab.="<td valign=top>".$bar['kota']."<br><br>1. ".$nmkaryawan[$bar['tandatangan']]."<br>2. ".$nmkaryawan[$bar['tandatangan2']]."</td>";
				$tab.="<td valign=top>".number_format($bar['rupiah'])."</td>";
				$tab.="<td valign=top>".$bar['sample']."</td>";
				$tab.="<td valign=top>".$bar['pekerjaan']."</td>";
			$tab.="<td align=center>";
				if($bar['posting']==0){
					 $tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
				onclick=\"fillField('".$bar['nokontrak']."','".$bar['jenis']."');\">";
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\">";		
									
			$tab.="&nbsp;<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\">";							
							} else{
								$tab.="&nbsp;<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' >";
							}
					
						  $tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$bar['nokontrak']."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\">";	
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
		."###".$bar['jenis']."###".tanggalnormal($bar['tanggal'])."###".$bar['surveyor']."###".$bar['kuantitas']
		."###".tanggalnormal($bar['tanggalpelaksanaan'])."###".$bar['kota']."###".$bar['tandatangan']."###".$bar['tandatangan2']
		."###".$bar['rupiah']."###".$bar['sample']."###".$bar['parameter']
		."###".$bar['tempatpelaksanaan']."###".$bar['pelabuhantujuan']."###".$bar['pekerjaan']."###".$bar['namaponton']."###".$bar['nosip']
		."###".$bar['transportir']."###".$bar['namakapal'];
		
	break;
    default:
	break;
}
?>
