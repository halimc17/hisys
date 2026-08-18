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
// $tanggalkontrak = checkPostGet('tanggalkontrak','');
$tanggalkontrak       =tanggalsystemn(checkPostGet('tanggalkontrak',''));
$kodecustomer = checkPostGet('kodecustomer','');
$kodebarang = checkPostGet('kodebarang','');
$nospk = checkPostGet('nospk','');
$jenis = checkPostGet('jenis','');
// $tanggal = checkPostGet('tanggal','');
$tanggal       =tanggalsystemn(checkPostGet('tanggal',''));
$transportir = checkPostGet('transportir','');
$kuantitas = checkPostGet('kuantitas','');
$kuantitaskemasan = checkPostGet('kuantitaskemasan','');
$pelabuhanmuat = checkPostGet('pelabuhanmuat','');
$pelabuhantujuan = checkPostGet('pelabuhantujuan','');
// $tanggalmuat1 = checkPostGet('tanggalmuat1','');
$tanggalmuat1       =tanggalsystemn(checkPostGet('tanggalmuat1',''));
$tanggalmuat2       =tanggalsystemn(checkPostGet('tanggalmuat2',''));
// $tanggalmuat2 = checkPostGet('tanggalmuat2','');
$namakapal = checkPostGet('namakapal','');
$tandatangan = checkPostGet('tandatangan','');
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
where a.status=1 and b.tipe in ('TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmtransportir[$bar['supplierid']]=$bar['namasupplier'];
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
	
	
		#= query
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
			$kuantitaskemasan=$bar['kuantitaskemasan'];
			$pelabuhanmuat=$bar['pelabuhanmuat'];
			$pelabuhantujuan=$bar['pelabuhantujuan'];
			$tanggalmuat1=$bar['tanggalmuat1'];
			$tanggalmuat2=$bar['tanggalmuat2'];
			
			$tandatangan=$bar['tandatangan'];
			$rupiah=$bar['rupiah'];
			$kota=$bar['kota'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$transportir."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$transportirnama=$bar['namasupplier'];	

		$str = "select * from ".$dbname.".log_5supalamat where supplierid='".$transportir."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$transportirkota=$bar['kota'];
			
		$str = "select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='KANWIL'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kotaro=$bar['wilayahkota'];
			$alamatro=$bar['alamat'];
			$telpro=$bar['telepon'];
			$faxro=$bar['fax'];
	
			//	// size: 21.5cm 13.5cm;
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

		$cellpadding=1;
		
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
		
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Kepada Yth :</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>Perusahaan Pelayaran ".$transportirnama."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>di</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>&nbsp;&nbsp;&nbsp;&nbsp;Tempat</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td>".$transportirkota."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Dengan ini diberitahukan bahwa kami akan melaksanakan pemuatan kargo seperti tercantum dibawah ini :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$cellpadding=5;
		
		//style='font-size:12px;margin-left:25%;'
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:20px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='width:150px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kontrak No</td>"; 
				$tab.="<td style='width:30px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='width:350px;border:1px solid #000000;'>".$nokontrak."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Nama Pengirim</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmpt[$kodept]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['komoditi']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['kuantitas']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/- ".number_format($kuantitas)." Kg</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['kuantitaskemasan']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/- ".number_format($kuantitaskemasan)." Kg</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['pelabuhanmuat']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmfranco[$pelabuhanmuat]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['pelabuhantujuan']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmfranco[$pelabuhantujuan]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Nama Penerima</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmcustsomer[$kodecustomer]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Rencana Muat</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".tglnmblnhr($tanggalmuat1,'long','I')." s/d ".tglnmblnhr($tanggalmuat2,'long','I')."</td>"; 
			$tab.="</tr>";
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			
			
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jenis Angkutan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$kapalponton."</td>"; 
			$tab.="</tr>";
			
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$cellpadding=3;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Demikianlah pemberitahuan ini kami buat agar dipergunakan sebagaimana layaknya.</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='heigh:50px'>&nbsp;</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$tab.="<td>".$kota.", ".tglnmbln($tanggal,'long','I')."</td>"; 
			$tab.="</tr>";

		for($i=1;$i<7;$i++){
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
		
		
		for($i=1;$i<10;$i++){
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
		
		#= alamat RO ambil dari RO dengan induk PT
		
		
		
		
		
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
				`nospk`, `jenis`, `tanggal`, `transportir`, `kuantitas`, `kuantitaskemasan`,
				`pelabuhanmuat`, `pelabuhantujuan`, `tanggalmuat1`, `tanggalmuat2`, `namakapal`,`namaponton`,
				`tandatangan`,`rupiah`,`kota`,
				`createby`, `createtime`, `updateby`)
				values (
				'".$nokontrak."','".$kodept."','".$tanggalkontrak."','".$kodecustomer."','".$kodebarang."',
				'".$nospk."','".$jenis."','".$tanggal."','".$transportir."','".$kuantitas."','".$kuantitaskemasan."',
				'".$pelabuhanmuat."','".$pelabuhantujuan."','".$tanggalmuat1."','".$tanggalmuat2."','".$namakapal."','".$namaponton."',
				'".$tandatangan."','".$rupiah."','".$kota."',
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
			kuantitaskemasan='".$kuantitaskemasan."',
			pelabuhanmuat='".$pelabuhanmuat."',
			pelabuhantujuan='".$pelabuhantujuan."',
			tanggalmuat1='".$tanggalmuat1."',
			tanggalmuat2='".$tanggalmuat2."',
			namakapal='".$namakapal."',
			namaponton='".$namaponton."',
			tandatangan='".$tandatangan."',
			kota='".$kota."',
			rupiah='".$rupiah."'
			where nospk = '".$nospk."' and nokontrak='".$nokontrak."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
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
		

   case'loaddata':
		

		$tab="<table class=sortable cellpadding=5 cellspacing=1 border=0 style='width:100%;'>
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
				<th align=center>".$_SESSION['lang']['kuantitaskemasan']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['pelabuhanmuat']."</th>
				<th align=center>".$_SESSION['lang']['pelabuhantujuan']."</th>
				<th align=center>".$_SESSION['lang']['tanggalmuat']."</th>
				<th align=center>".$_SESSION['lang']['namakapal']."<br>".$_SESSION['lang']['namaponton']." </th>
				<th align=center>".$_SESSION['lang']['tandatangan']."</th>
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
			$tab.="<tr class=rowcontent>";
				// $tab.="<td align=center>".$no.".</td>";
				$tab.="<td>".$bar['nokontrak']."</td>";
				$tab.="<td>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggalkontrak'])."</td>";
				$tab.="<td>".$nmcustsomer[$bar['kodecustomer']]."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td>".$bar['nospk']."</td>";
				$tab.="<td>".$namajenis[$bar['jenis']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$nmtransportir[$bar['transportir']]."</td>";
				$tab.="<td>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td>".number_format($bar['kuantitaskemasan'])."</td>";
				$tab.="<td>".$nmfranco[$bar['pelabuhanmuat']]."</td>";
				$tab.="<td>".$nmfranco[$bar['pelabuhantujuan']]."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['tanggalmuat1'])."<br>s/d<br>".tanggalnormal($bar['tanggalmuat2'])."</td>";
				$tab.="<td>".$nmkapalponton[$bar['namakapal']]."<br>".$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td>".$nmkaryawan[$bar['tandatangan']]."</td>";
				$tab.="<td>".$bar['kota']."</td>";

		
		
		
		
		
		
		// $tab.="<td align=center width=25px>";
				if($bar['posting']==0){
					 $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['kodecustomer']."','".$bar['kodebarang']."','".$bar['nospk']."','".$bar['jenis']."','".tanggalnormal($bar['tanggal'])."','".$bar['transportir']."','".number_format($bar['kuantitas'])."','".number_format($bar['kuantitaskemasan'])."','".$bar['pelabuhanmuat']."','".$bar['pelabuhantujuan']."','".tanggalnormal($bar['tanggalmuat1'])."','".tanggalnormal($bar['tanggalmuat2'])."','".$bar['namakapal']."','".$bar['tandatangan']."','".$bar['rupiah']."','".$bar['kota']."','".$bar['namaponton']."');\"></td>";
				
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\"></td>";		
						
					$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";							
				} else{
					$tab.="<td></td>";
					$tab.="<td></td>";
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

    default:
	break;
}




?>
