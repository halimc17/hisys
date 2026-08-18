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
$tanggalkedatangan1              = tanggalsystemn(checkPostGet('tanggalkedatangan1',''));
$tanggalkedatangan2              = tanggalsystemn(checkPostGet('tanggalkedatangan2',''));
$transportir                    = checkPostGet('transportir','');
$kuantitas                      = checkPostGet('kuantitas','');
$kuantitaskemasan               = checkPostGet('kuantitaskemasan','');
$pelabuhanmuat                  = checkPostGet('pelabuhanmuat','');
$pelabuhanbongkar               = checkPostGet('pelabuhanbongkar','');
$namakapal                      = checkPostGet('namakapal','');
$namaponton                      = checkPostGet('namaponton','');
$tandatangan                    = checkPostGet('tandatangan','');
$surveyor                       = checkPostGet('surveyor','');
$rupiah                         = checkPostGet('rupiah','');
$asalkargo                      = checkPostGet('asalkargo','');
$bongkarmuat                      = checkPostGet('bongkarmuat','');
$kota                      = checkPostGet('kota','');

$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');
$nmjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where b.status=1 and b.tipe in ('JASAANALISA','JASABONGKARMUAT','TRANSPORTIR') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
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
			$asalkargo=$bar['asalkargo'];
			$tanggalkedatangan1=$bar['tanggalkedatangan1'];
			$tanggalkedatangan2=$bar['tanggalkedatangan2'];
			$tandatangan=$bar['tandatangan'];
			$rupiah=$bar['rupiah'];
			$bongkarmuat=$bar['bongkarmuat'];
			$kota=$bar['kota'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];


	
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
			
		$str = "select * from ".$dbname.".log_5supalamat where supplierid='".$transportir."'";
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
				$tab.="<td>Dengan ini ".$namapt." menunjuk ".$namabongkarmuat." untuk melaksanakan pemuatan kargo seperti
						tercantum dibawah ini :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<br>";
		
		$cellpadding=10;
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:20px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='width:200px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jenis Komoditi</td>"; 
				$tab.="<td style='width:30px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='width:350px;border:1px solid #000000;'>".$nmkomoditi[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kuantitas</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>+/-&nbsp;&nbsp; ".number_format($kuantitas)." Kg</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Kontrak Penjualan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$nokontrak."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Asal Kargo</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$asalkargo."</td>"; 
			$tab.="</tr>";
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jenis Angkutan</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$kapalponton."</td>";  
			$tab.="</tr>";
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jadwal kedatangan Kapal (Pembeli)<br>Hari / Tanggal</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".tglnmblnhr($tanggalkedatangan1,'','')." - ".tglnmblnhr($tanggalkedatangan2,'','')."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$no++;
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center align=center>".$no.".</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Agen Kapal</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' align=center>:</td>"; 
				$tab.="<td style='border:1px solid #000000;'>".$namatransportir."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		$tab.="<br>";
	
	
		$cellpadding=3;
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
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
		$tab.="</table>";
		
		
		for($i=1;$i<10;$i++){
			$tab.="<br>";
		}
	
		
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
	
	
	
	
	
	
	
	
	
	case 'insert':
	
		#= cek apakah sudah ada spk untuk kontrak ini
		// $str = "select count(*) as jumlah from ".$dbname.".".$table."  where nokontrak='".$nokontrak."'";
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
			// if($bar['jumlah']>0){
				// exit("Error : Sudah ada SPK ".$namajenis[$jenis]."  untuk kontrak ".$nokontrak." ");
			// }
			
		#generet nokontrak	
		
		if($nospk==''){
			$nospk = generatenospk();		
		}
		
		// exit("Error:A");
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
					'asalkargo' => $asalkargo,
					'namakapal' => $namakapal,
					'tanggalkedatangan1' => $tanggalkedatangan1,
					'tanggalkedatangan2' => $tanggalkedatangan2,
					'tandatangan' => $tandatangan,
					'rupiah' => $rupiah,
					'bongkarmuat' => $bongkarmuat,
					'kota' => $kota,
					'createby' => $_SESSION['standard']['userid'],
					'createtime' => date('Y-m-d H:i'),
					'updateby' => $_SESSION['standard']['userid']
					);
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols);
		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}

	break;
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
			tanggal='".$tanggal."',
			transportir='".$transportir."',
			kuantitas='".$kuantitas."',
			asalkargo = '".$asalkargo."',
			namakapal = '".$namakapal."',
			tanggalkedatangan1 ='".$tanggalkedatangan1."',
			tanggalkedatangan2 ='".$tanggalkedatangan2."',
			namakapal='".$namakapal."',
			tandatangan='".$tandatangan."',
			bongkarmuat='".$bongkarmuat."',
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
				<th align=center>".$_SESSION['lang']['NoKontrak']." <br>".$_SESSION['lang']['kodept']."</th>
				<th align=center>".$_SESSION['lang']['tglKontrak']." <br>".$_SESSION['lang']['Pembeli']."</th>
				<th align=center>".$_SESSION['lang']['komoditi']."</th>
				<th align=center>".$_SESSION['lang']['nospk']."<br>".$_SESSION['lang']['jenis']."</th>
				<th align=center>".$_SESSION['lang']['tanggal']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."</th>
				<th align=center>".$_SESSION['lang']['bongkarmuat']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>Asal Cargo</th>
				<th align=center>".$_SESSION['lang']['namakapal']."</th>
				<th align=center width=70px colspan=2>Tgl Kapal Datang</th>
				<th align=center>".$_SESSION['lang']['tandatangan']."</th>
				<th align=center>".$_SESSION['lang']['kota']."</th>
				<th align=center>".$_SESSION['lang']['rupiah']."</th>
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
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$nmsupplier[$bar['transportir']]."</td>";
				$tab.="<td>".$nmsupplier[$bar['bongkarmuat']]."</td>";
				$tab.="<td>".number_format($bar['kuantitas'])."</td>";
				$tab.="<td>".$bar['asalkargo']."</td>";
				$tab.="<td>".$bar['namakapal']."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggalkedatangan1'])."</td>"; 
				$tab.="<td>".tanggalnormal($bar['tanggalkedatangan2'])."</td>";
				$tab.="<td>".$nmkaryawan[$bar['tandatangan']]."</td>";
				$tab.="<td>".$bar['kota']."</td>";
				$tab.="<td>".number_format($bar['rupiah'],2)."</td>";
				
				
				// $tab.="<td align=center>";
				if($bar['posting']==0){
					$tab.="<td valign=top align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['kodecustomer']."','".$bar['kodebarang']."','".$bar['nospk']."','".$bar['jenis']."', '".tanggalnormal($bar['tanggal'])."','".$bar['transportir']."','".$bar['kuantitas']."','".$bar['asalkargo']."','".$bar['namakapal']."','".tanggalnormal($bar['tanggalkedatangan1'])."','".tanggalnormal($bar['tanggalkedatangan2'])."','".$bar['tandatangan']."','".$bar['rupiah']."','".$bar['bongkarmuat']."','".$bar['kota']."');\"></td>";
					 
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\"></td>";		
					
					$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";							
				} else{
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
		
			$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$bar['nokontrak']."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";	
			
			$tab.="<td align=center width=25px><img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$bar['nospk']."','SALES_".$bar['jenis']."') title=Upload></td>";
			
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
