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
$tandatangan1 = checkPostGet('tandatangan1','');
$tandatangan2 = checkPostGet('tandatangan2','');
$rupiah = checkPostGet('rupiah','');
$kota = checkPostGet('kota','');
$namaponton = checkPostGet('namaponton','');
$harga = checkPostGet('harga','');
$lain = checkPostGet('lain','');
$transportirdarat = checkPostGet('transportirdarat','');
$nospksch = checkPostGet('nospksch','');
$kodeptsch = checkPostGet('kodeptsch','');

$rpkg = checkPostGet('rpkg','');
$rpkg = str_replace(',','',$rpkg);
$toleransi = checkPostGet('toleransi','');
$kgtoleransi = checkPostGet('kgtoleransi','');
$noakun = checkPostGet('noakun','');

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
			
			$tandatangan1=$bar['tandatangan1'];
			$tandatangan2=$bar['tandatangan2'];
			$rupiah=$bar['rupiah'];
			$kota=$bar['kota'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			
			$transportirdarat=$bar['transportirdarat'];
			$harga=$bar['harga'];
			$lain=$bar['lain'];
			
		$str = "select * from ".$dbname.".log_5supplier where supplierid in ('".$transportir."','".$transportirdarat."')";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$transportirnama[$bar['supplierid']]=$bar['namasupplier'];	
		}
			
	
		$str = "select * from ".$dbname.".log_5supalamat where supplierid in ('".$transportir."','".$transportirdarat."')";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		while($bar=$res->fetch()){
			$transportirkota[$bar['supplierid']]=$bar['kota'];	
		}
		
			
		$str = "select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='KANWIL'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kotaro=$bar['wilayahkota'];
			$alamatro=$bar['alamat'];
	
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
		
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Dengan ini ".$nmpt[$kodept].", menunjuk ".$transportirnama[$transportirdarat]." 
						selaku transportir untuk melaksanakan pengangkutan ".$arrinisial[$kodebarang]." 
						dari ".$nmfranco[$pelabuhanmuat]." ke ".$nmfranco[$pelabuhantujuan]." sesuai dengan
						yang telah disepakati sebelumnya sebagai berikut :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$cellpadding=5;
		
		//style='font-size:12px;margin-left:25%;'
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
			// $tab.="<tr>";
				// $no=1;
				// $tab.="<td style='width:20px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				// $tab.="<td style='width:150px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kotrak No</td>"; 
				// $tab.="<td style='width:30px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				// $tab.="<td style='width:350px;border:1px solid #000000;'>".$nokontrak."</td>"; 
			// $tab.="</tr>";
			
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:20px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='width:150px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['komoditi']."</td>"; 
				$tab.="<td style='width:30px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='width:350px;border:1px solid #000000;'>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['kuantitas']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/- ".number_format($kuantitas)." Kg</td>"; 
			$tab.="</tr>";
			
	
			
			$no++;
			$tab.="<tr valign=top>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['bongkarmuat']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nmfranco[$pelabuhanmuat]." ke ".$nmfranco[$pelabuhantujuan]."</td>"; 
			$tab.="</tr>";
			
			
			if($transportirdarat!=''){
				$no++;
				$tab.="<tr valign=top>";
					$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Alat Pengangkutan</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
					$tab.="<td style='border:1px solid #000000;'>Truck</td>"; 
				$tab.="</tr>";
			} else {
				$no++;
				$tab.="<tr valign=top>";
					$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Nama Kapal / Ponton</td>"; 
					$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
					$tab.="<td style='border:1px solid #000000;'>".$kapalponton."</td>"; 
				$tab.="</tr>";
			}
			
		
			$no++;
			$tab.="<tr valign=top>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Waktu Pengangkutan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".tglnmblnhr($tanggalmuat1,'long','I')." s/d ".tglnmblnhr($tanggalmuat2,'long','I')."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr valign=top>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['harga']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$harga."</td>"; 
			$tab.="</tr>";
			
		
			$no++;
			$tab.="<tr valign=top>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['lain']."</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".nl2br($lain)."</td>"; 
			$tab.="</tr>";
			
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$cellpadding=3;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td colspan=3>Note : Mohon melampirkan SPK ini pada saat penagihan</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
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
		$tab.="<table width=100%  style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
			$tab.="<tr>";
		
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000;' align=center>".$nmkaryawan[$tandatangan1]."</td>"; 
				$tab.="<td style='width:200px'>&nbsp;</td>"; 
				if($tandatangan2!='0000000000'){
					$tab.="<td style='width:200px;border-bottom:0.5px solid #000000;' align=center>".$nmkaryawan[$tandatangan2]."</td>"; 
				}else{
					$tab.="<td style='width:200px'>&nbsp;</td>"; 
				}
				
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan1]]."</td>"; 
				$tab.="<td style='width:200px'>&nbsp;</td>"; 
				if($tandatangan2!='0000000000'){
					$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan2]]."</td>";  
				}else{
					$tab.="<td style='width:200px'>&nbsp;</td>"; 
				}
			$tab.="</tr>";
	
		$tab.="</table>";
		$tab.="<br>";
		
		
		$tab.="<table width=30%  style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
		$tab.="<tr>";		
		$tab.="<td>CC :</td>"; 
				$tab.="<td>- SMM PMKS ".$kodept."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>- GM Finance</td>"; 
			$tab.="</tr>";	
				$tab.="<tr>";
				$tab.="<td></td>"; 
				$tab.="<td>- Manager IBW</td>"; 
			$tab.="</tr>";		
		$tab.="</table>";
		
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
				`tandatangan1`,`tandatangan2`,`rupiah`,`kota`,
				`transportirdarat`, `harga`, `lain`,`rpkg`,`toleransi`,`kgtoleransi`,`noakundebet`,
				`createby`, `createtime`, `updateby`)
				values (
				'".$nokontrak."','".$kodept."','".$tanggalkontrak."','".$kodecustomer."','".$kodebarang."',
				'".$nospk."','".$jenis."','".$tanggal."','".$transportir."','".$kuantitas."','".$kuantitaskemasan."',
				'".$pelabuhanmuat."','".$pelabuhantujuan."','".$tanggalmuat1."','".$tanggalmuat2."','".$namakapal."','".$namaponton."',
				'".$tandatangan1."','".$tandatangan2."','".$rupiah."','".$kota."',
				'".$transportirdarat."','".$harga."','".$lain."','".$rpkg."','".$toleransi."','".$kgtoleransi."','".$noakun."',
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
			tandatangan1='".$tandatangan1."',
			tandatangan2='".$tandatangan2."',
			kota='".$kota."',
			rupiah='".$rupiah."',
			
			transportirdarat='".$transportirdarat."',
			harga='".$harga."',
			lain='".$lain."',
			rpkg='".$rpkg."',
			toleransi='".$toleransi."',
			kgtoleransi='".$kgtoleransi."',
			noakundebet='".$noakun."'
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
		

		$limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
		$maxdisplay=($page*$limit);
		
		if($nospksch!=''){
			$where.=" and nospk like '%".$nospksch."%'";
		}
		
		if($kodeptsch!=''){
			$where.=" and kodept='".$kodeptsch."'";
		}	
		
		
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where nokontrak='' ".$where." ";
			// exit("Error".$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
            $jumrow = $bar['jumrow'];
			
			
		$no = 0;
		$no=$maxdisplay;
		#-GSW Add order by
		$str = "select *,left(nospk,3) as filternomor, right(nospk,4) as filtertahun from ".$dbname.".".$table." where nokontrak=''  ".$where." order by filtertahun desc,filternomor desc  limit " . $offset . "," . $limit . " ";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent style=vertical-align:top>";
				$tab.="<td align=center>".$no.".</td>";
				$tab.="<td>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td>".$bar['nospk']."<br><br>".$namajenis[$bar['jenis']]."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".@$nmtransportir[$bar['transportir']]."<br><br>".@$nmtransportir[$bar['transportirdarat']]."</td>";
				$tab.="<td align=right>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td align=right>".number_format($bar['kuantitaskemasan'])."</td>";
				$tab.="<td>".$nmfranco[$bar['pelabuhanmuat']]."<br><br>".$nmfranco[$bar['pelabuhantujuan']]."</td>";
				$tab.="<td  align=center>".tanggalnormal($bar['tanggalmuat1'])."<br>s/d<br>".tanggalnormal($bar['tanggalmuat2'])."</td>";
				$tab.="<td>".@$nmkapalponton[$bar['namakapal']]."<br><br>".@$nmkapalponton[$bar['namaponton']]."</td>";
				$tab.="<td>".$bar['harga']."</td>";
				$tab.="<td>".$bar['lain']."</td>";
				$tab.="<td>".@$nmkaryawan[$bar['tandatangan1']]."<br>".@$nmkaryawan[$bar['tandatangan2']]."</td>";
				
				$tab.="<td>".$bar['kota']."</td>";

		
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
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where nokontrak=''";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $bar = owlBaris($res);
        $totrows = ceil($bar / $limit);
		
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=21 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
	break;
	break;
	
	case'getEditData':
	
		$str = "select * from ".$dbname.".".$table."  where nospk='".$nospk."' and jenis='".$jenis."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			
		echo $bar['kodept']."###".tanggalnormal($bar['tanggalkontrak'])."###".$bar['kodecustomer']."###".$bar['kodebarang']."###".$bar['nospk']."###".
			$bar['jenis']."###".tanggalnormal($bar['tanggal'])."###".$bar['transportir']."###".number_format($bar['kuantitas'])."###".$bar['kuantitaskemasan']."###".
			$bar['pelabuhanmuat']."###".$bar['pelabuhantujuan']."###".tanggalnormal($bar['tanggalmuat1'])."###".tanggalnormal($bar['tanggalmuat2'])."###".$bar['namakapal']."###".
			$bar['tandatangan1']."###".$bar['rupiah']."###".$bar['kota']."###".$bar['namaponton']."###".$bar['transportirdarat']."###".
			$bar['harga']."###".$bar['lain']."###".$bar['tandatangan2']."###".$bar['rpkg']."###".$bar['toleransi']."###".$bar['kgtoleransi']."###".$bar['noakundebet'];
	break;

    default:
	break;
}




?>
