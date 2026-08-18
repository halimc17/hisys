<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');

$nokontrak = checkPostGet('nokontrak','');
$kodept = checkPostGet('kodept','');
$tanggalkontrak       =tanggalsystemn(checkPostGet('tanggalkontrak',''));
$kodecustomer = checkPostGet('kodecustomer','');
$kodebarang = checkPostGet('kodebarang','');
$nospk = checkPostGet('nospk','');
$jenis = checkPostGet('jenis','');
$tanggal       =tanggalsystemn(checkPostGet('tanggal',''));
$transportir = checkPostGet('transportir','');
$kuantitas = checkPostGet('kuantitas','');
$kuantitaskemasan = checkPostGet('kuantitaskemasan','');
$pelabuhanmuat = checkPostGet('pelabuhanmuat','');
$pelabuhanbongkar = checkPostGet('pelabuhanbongkar','');
$namakapal = checkPostGet('namakapal','');
$tandatangan = checkPostGet('tandatangan','');
$surveyor = checkPostGet('surveyor','');
$rupiah = checkPostGet('rupiah','');
$kota = checkPostGet('kota','');
$namaponton = checkPostGet('namaponton','');


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
			$kuantitas=$bar['kuantitas'];
			$pelabuhanmuat=$bar['pelabuhanmuat'];
			$pelabuhanbongkar=$bar['pelabuhanbongkar'];
			
			$tandatangan=$bar['tandatangan'];
			$kota=$bar['kota'];
			$surveyor=$bar['surveyor'];
			$rupiah=$bar['rupiah'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			
		#= nokontrak dipindah
		$listnokontrak="";
		$str = "select * from ".$dbname.".".$table." where  nospk='".$nospk."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$listnokontrak.=$bar['nokontrak']."<br>";
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
		
		$tab.="<table style='font-size:12px'  width=100% border=0 cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td colspan=3>Yang bertanda tangan dibawah ini :</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:110px'>Nama</td>"; 
				$tab.="<td style='width:5px' align=center>:</td>"; 
				$tab.="<td style='width:350px'>".$nmkaryawan[$tandatangan]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>Jabatan</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";

		$tab.="<br>";
		
		$tab.="<table style='font-size:12px'  width=100% border=0 cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td colspan=3>Bertindak untuk dan atas nama perusahaan :</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:110px'>Nama Perusahaan</td>"; 
				$tab.="<td style='width:5px' align=center>:</td>"; 
				$tab.="<td style='width:350px'>".$namapt."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td valign=top>Alamat</td>"; 
				$tab.="<td align=center  valign=top>:</td>"; 
				$tab.="<td valign=top>".$alamatpt."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>Telepon & Fax</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$telppt." / ".$faxpt."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:600px'>Memberitahukan bahwa komoditas kelapa sawit dan / atau produk turunannya akan 
						diantar pulaukan dengan rincian sebagai berikut :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<br>";
		
		
		
		
		$tab.="<table style='font-size:12px'  width=100% border=0 cellpadding=".$cellpadding.">";		
			$tab.="<tr>";
				$tab.="<td align=left>1.</td>"; 
				$tab.="<td colspan=4>Data Komoditas</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:5px'></td>"; 
				$tab.="<td style='width:5px'>a.</td>"; 
				$tab.="<td  style='width:20px'>Jenis komoditas</td>"; 
				$tab.="<td align=center style='width:5px'>:</td>"; 
				$tab.="<td style='width:300px'>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td valign=top></td>"; 
				$tab.="<td valign=top>b.</td>"; 
				$tab.="<td valign=top>Jumlah</td>"; 
				$tab.="<td align=center valign=top>:</td>"; 
				$tab.="<td valign=top>+/- ".number_format($kuantitas)." Kg &nbsp;&nbsp;;&nbsp; Kontrak No :<br>".$listnokontrak."</td>"; 
			$tab.="</tr>";
			
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>c.</td>"; 
				$tab.="<td>Jenis Angkutan</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$kapalponton."</td>"; 
			$tab.="</tr>";
			// $tab.="<tr>";
				// $tab.="<td>&nbsp;</td>"; 
			// $tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>d.</td>"; 
				$tab.="<td>Pelabuhan Muat</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$nmfranco[$pelabuhanmuat]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>e.</td>"; 
				$tab.="<td>Pelabuhan Bongkar</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$nmfranco[$pelabuhanbongkar]."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td align=center>2.</td>"; 
				$tab.="<td colspan=4>Data Penerima</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>a.</td>"; 
				$tab.="<td>Nama Perusahaan</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$namacustomer."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td valign=top>b.</td>"; 
				$tab.="<td valign=top>Alamat Perusahaan</td>"; 
				$tab.="<td valign=top align=center>:</td>"; 
				$tab.="<td valign=top>".$alamatcustomer."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>c.</td>"; 
				$tab.="<td>Telepon & Fax</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$telpcustomer." / ".$faxcustomer."</td>"; 
			$tab.="</tr>";
			
			
				$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			
			
			$tab.="<tr>";
				$tab.="<td align=center>3.</td>"; 
				$tab.="<td colspan=4>Data Surveyor</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>a.</td>"; 
				$tab.="<td>Nama Perusahaan</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$namasurveyor."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td valign=top>b.</td>"; 
				$tab.="<td valign=top>Alamat Perusahaan</td>"; 
				$tab.="<td valign=top align=center>:</td>"; 
				$tab.="<td valign=top>".$alamatsurveyor."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>c.</td>"; 
				$tab.="<td>Telepon & Fax</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td>".$telpsurveyor." / ".$faxsurveyor."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$cellpadding=2;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Demikian disampaikan, apabila terdapat keterangan yang tidak benar dalam surat pemberitahuan
				ini, kami bersedia dikenakan sanksi dengan ketentuan yang berlaku.</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$tab.="<td>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
			$tab.="</tr>";

		for($i=1;$i<5;$i++){
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			}
		$tab.="</table>";	
			
		$cellpadding=0.5;	
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";	
			$tab.="<tr>";
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		
		
		for($i=1;$i<5;$i++){
			$tab.="<br>";
		}
		
		$tab.="<footer>";
			$cellpadding=1;	
			$tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
				$tab.="<tr>";
					$tab.="<td align=left style='width:700px;border-bottom:0.5px solid #000000'><b>".$nmpt[$kodept]."</b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					// $tab.="<td align=left style='width:120px'><b>".$kotaro." Office</b></td>"; 
					// $tab.="<td align=center style='width:5px'>:</td>"; 
					// $tab.="<td align=left>".$alamatro."</td>"; 
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
	
	
	case 'insert':
	
		
		#generet nokontrak	
		$nospk = generatenospk();		
	
		$str = "insert into  ".$dbname.".".$table." 
				(`nokontrak`, `kodept`, `tanggalkontrak`, `kodecustomer`, `kodebarang`,
				`nospk`, `jenis`, `tanggal`, `transportir`, `kuantitas`,
				`pelabuhanmuat`, `pelabuhanbongkar`, `namakapal`,`namaponton`,
				`tandatangan`,`surveyor`,`rupiah`,`kota`, 
				`createby`, `createtime`, `updateby`)
				values (
				'".$nokontrak."','".$kodept."','".$tanggalkontrak."','".$kodecustomer."','".$kodebarang."',
				'".$nospk."','".$jenis."','".$tanggal."','".$transportir."','".$kuantitas."',
				'".$pelabuhanmuat."','".$pelabuhanbongkar."','".$namakapal."','".$namaponton."',
				'".$tandatangan."','".$surveyor."','".$rupiah."','".$kota."',
				'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."')";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
			tanggal='".$tanggal."',
			transportir='".$transportir."',
			kuantitas='".$kuantitas."',
			pelabuhanmuat='".$pelabuhanmuat."',
			pelabuhanbongkar='".$pelabuhanbongkar."',
			namakapal='".$namakapal."',
			namaponton='".$namaponton."',
			tandatangan='".$tandatangan."',
			surveyor='".$surveyor."',
			rupiah='".$rupiah."',
			kota='".$kota."'
			where nospk = '".$nospk."' and nokontrak='".$nokontrak."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;

   case'loaddata':
	
		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0>
			<thead>
			<tr class=rowheader>
				<th align=center>".$_SESSION['lang']['NoKontrak']."</th>
				<th align=center>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['tglKontrak']."</th>
				<th align=center>".$_SESSION['lang']['Pembeli']."</th>
				<th align=center>".$_SESSION['lang']['komoditi']."</th>
				<th align=center>".$_SESSION['lang']['nospk']."</th>
				<th align=center>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['pelabuhanmuat']."</th>
				<th align=center>".$_SESSION['lang']['pelabuhanbongkar']."</th>
				<th align=center>".$_SESSION['lang']['namakapal']."<br>".$_SESSION['lang']['namaponton']."</th>
				<th align=center>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>".$_SESSION['lang']['surveyor']."</th>
				<th align=center>".$_SESSION['lang']['kota']."</th>
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
				// $tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['nokontrak']."</td>";
				$tab.="<td>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggalkontrak'])."</td>";
				$tab.="<td>".$nmcustsomer[$bar['kodecustomer']]."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td>".$bar['nospk']."</td>";
				$tab.="<td>".$namajenis[$bar['jenis']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$nmtransportir[$bar['transportir']]."</td>";
				$tab.="<td align=right>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td>".$nmfranco[$bar['pelabuhanmuat']]."</td>";
				$tab.="<td>".$nmfranco[$bar['pelabuhanbongkar']]."</td>";
				$tab.="<td>".$nmkapalponton[$bar['namakapal']]." / ".$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td>".$nmkaryawan[$bar['tandatangan']]."</td>";
				$tab.="<td>".$nmsurveyor[$bar['surveyor']]."</td>";
				$tab.="<td>".$bar['kota']."</td>";
				
						
				// $tab.="<td align=center>";
				if($bar['posting']==0){
					 $tab.="<td valign=top align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['kodecustomer']."','".$bar['kodebarang']."','".$bar['nospk']."','".$bar['jenis']."','".tanggalnormal($bar['tanggal'])."','".$bar['transportir']."','".$bar['kuantitas']."','".$bar['pelabuhanmuat']."','".$bar['pelabuhanbongkar']."','".$bar['namakapal']."','".$bar['tandatangan']."','".$bar['surveyor']."','".$bar['rupiah']."','".$bar['kota']."','".$bar['namaponton']."');\"></td>";
					 
					$tab.="<td valign=top align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\"></td>";		
					
					$tab.="<td valign=top align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";							
				} else{
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
		
				$tab.="<td valign=top align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$bar['nokontrak']."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";	
				
				$tab.="<td valign=top align=center width=25px><img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$bar['nospk']."','SALES_".$bar['jenis']."') title=Upload>";
			
								$tab.="</td>";
            $tab.="</tr>";
        }
		$tab.="</table>";
		
			
		
		
		
		
				
				
            // $tab.="<td><img src=images/application/application_edit.png class=resicon  caption='Edit' 
				// onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['kodecustomer']."','".$bar['kodebarang']."',
								// '".$bar['nospk']."','".$bar['jenis']."','".tanggalnormal($bar['tanggal'])."','".$bar['transportir']."','".$bar['kuantitas']."',
								// '".$bar['pelabuhanmuat']."','".$bar['pelabuhanbongkar']."',
								// '".$bar['namakapal']."','".$bar['tandatangan']."','".$bar['surveyor']."','".$bar['rupiah']."','".$bar['kota']."');\">";
			   // $tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$nokontrak."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\">";					
			// $tab.="</td>";
            // $tab.="</tr>";
        // }
		// $tab.="</table>";
		echo $tab;
	break;

    default:
	break;
}




?>
