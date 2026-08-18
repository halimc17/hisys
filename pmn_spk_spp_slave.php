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
$tanggalberlaku       =tanggalsystemn(checkPostGet('tanggalberlaku',''));
$transportir = checkPostGet('transportir','');
$kuantitas = checkPostGet('kuantitas','');
	$kuantitas=str_replace(',', '',$kuantitas);
$kuantitaskemasan = checkPostGet('kuantitaskemasan','');
$tarifkapal = checkPostGet('tarifkapal','');
	$tarifkapal=str_replace(',', '',$tarifkapal);
$tarifponton = checkPostGet('tarifponton','');
	$tarifponton=str_replace(',', '',$tarifponton);
$namakapal = checkPostGet('namakapal','');
$tandatangan = checkPostGet('tandatangan','');
$namaponton = checkPostGet('namaponton','');
$rupiah = checkPostGet('rupiah','');

$kota = checkPostGet('kota','');
$tandatangan2 = checkPostGet('tandatangan2','');
$keperluan = checkPostGet('keperluan','');


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
			$tanggalberlaku=$bar['tanggalberlaku'];
			$nokontrak=$bar['nokontrak'];
			$kodept=$bar['kodept'];
			$tanggalkontrak=$bar['tanggalkontrak'];
			$kodecustomer=$bar['kodecustomer'];
			$kodebarang=$bar['kodebarang'];
			$nospk=$bar['nospk'];
			$jenis=$bar['jenis'];
			$tanggal=$bar['tanggal'];
			$kuantitas=$bar['kuantitas'];
			$transportir=$bar['transportir'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			$keperluan=$bar['keperluan'];
			$tarifkapal=$bar['tarifkapal'];
			$tarifponton=$bar['tarifponton'];
			$tandatangan=$bar['tandatangan'];
			$tandatangan2=$bar['tandatangan2'];
			$rupiah=$bar['rupiah'];
			$kota=$bar['kota'];


	
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
		
		$cellpadding=3;
		$tab.="<table style='font-size:12px;border:1px solid #000000;' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>No.S.P.P</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".$nospk."</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center></td>"; 	
				$tab.="<td>".$_SESSION['lang']['tanggal']."</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".tglnmblnhr($tanggal,'long','i')."</td>"; 
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center></td>"; 	
				$tab.="<td></td>"; 
			$tab.="</tr>";
		$tab.="</table>";

		
		
		
		$tab.="<br>";	
		
		
		$tab.="<br>";
		
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				// $tab.="<td style='width:20px;' rowspan=2 align=center>&nbsp;</td>"; 
				$tab.="<td rowspan=2 valign=top style='width:136px;'>Kepada</td>"; 
				$tab.="<td style='width:10px' align=center rowspan=2 valign=top>:</td>"; 
				$tab.="<td  style='width:300px;border-bottom:0.5px solid #000000'>".$namatransportir."</td>"; 
				$tab.="<td  style='width:200px;' rowspan=2>&nbsp;</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td  style='width:200px;'>".$kptransportir."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$tab.="<table style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Dengan ini kami meminta kepada bapak untuk menyerahkan satu unit ponton dengan rincian sbb :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";	
		
		$tab.="<br>";
		
		
		
		
		$tab.="<table style='font-size:12px;border:1px solid #000000;width:600px;' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td align=center></td>"; 	
				$tab.="<td>".$_SESSION['lang']['tanggalpakai']."</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".tglnmblnhr($tanggalberlaku,'long','i')."</td>"; 
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>Nama Ponton</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".$namaponton."</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>Kapasitas Ponton</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>+/- ".number_format($kuantitas/1000)." MT ".$nmkomoditi[$kodebarang]."</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center valign=top>&nbsp;</td>"; 
				$tab.="<td style='width:110px;' valign=top>Keperluan</td>"; 
				$tab.="<td style='width:10px' align=center  valign=top>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".$keperluan."</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>Untuk Kontrak No</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".$nokontrak."</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>Tarif</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>Rp ".number_format($tarifponton)." Per hari</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center></td>"; 	
				$tab.="<td></td>"; 
			$tab.="</tr>";
			$tab.="</table>";
			
			// $tab.="<br>";
			$tab.="<br>";
			
			$tab.="<table style='font-size:12px;border:1px solid #000000;width:600px;' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:20px;' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:110px;'>Nama Kapal Tarik</td>"; 
				$tab.="<td style='width:10px' align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>".$namakapal."</td>"; 
					$tab.="<td  style='width:200px;'>&nbsp;</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center>&nbsp;</td>"; 
				$tab.="<td>Tarif</td>"; 
				$tab.="<td align=center>:</td>"; 
				$tab.="<td  style='width:300px;000000;border-bottom:0.5px solid #000000;'>Rp ".number_format($tarifkapal)." PP</td>"; 
				$tab.="<td  style='width:200px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center></td>"; 	
				$tab.="<td></td>"; 
			$tab.="</tr>";
			$tab.="</table>";		
			
			// $tab.="<tr>";
				// $tab.="<td align=center></td>"; 	
				// $tab.="<td></td>"; 
			// $tab.="</tr>";
		// $tab.="</table>";
		
		
	
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
	
		
	
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
				$tab.="<td style='width:200px;border-bottom:0.5px solid #000000' align=center>".$nmkaryawan[$tandatangan2]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan]]."</td>"; 
				$tab.="<td style='width:200px' align=center></td>"; 
				$tab.="<td style='width:200px' align=center>".$nmjabatan[$kodejabatan[$tandatangan2]]."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		
		
		for($i=1;$i<12;$i++){
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
				// exit("Error:Sudah ada SPK ".$namajenis[$jenis]."  untuk kontrak ".$nokontrak." ");
			// }
			
		#generet nokontrak	
		$nospk = generatenospk();		
	
		$str = "insert into  ".$dbname.".".$table." 
				(`nokontrak`, `kodept`, `tanggalkontrak`, `kodecustomer`, `kodebarang`,
				`nospk`, `jenis`, `tanggal`, `transportir`, `kuantitas`,
				`tarifkapal`, `tarifponton`, `namakapal`,
				`tandatangan`,`namaponton`,`rupiah`,`kota`,`keperluan`,`tandatangan2`,
				`createby`, `createtime`, `updateby`,`tanggalberlaku`)
				values (
				'".$nokontrak."','".$kodept."','".$tanggalkontrak."','".$kodecustomer."','".$kodebarang."',
				'".$nospk."','".$jenis."','".$tanggal."','".$transportir."','".$kuantitas."',
				'".$tarifkapal."','".$tarifponton."','".$namakapal."',
				'".$tandatangan."','".$namaponton."','".$rupiah."','".$kota."','".$keperluan."','".$tandatangan2."',
				'".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."','".$_SESSION['standard']['userid']."','".$tanggalberlaku."')";
		
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
			tarifkapal='".$tarifkapal."',
			tarifponton='".$tarifponton."',
			namakapal='".$namakapal."',
			tandatangan='".$tandatangan."',
			namaponton='".$namaponton."',
			rupiah='".$rupiah."',
			kota='".$kota."',
			keperluan='".$keperluan."',
			tandatangan2='".$tandatangan2."',
			tanggalberlaku='".$tanggalberlaku."'
			where nospk = '".$nospk."' and nokontrak='".$nokontrak."'";
					// exit("Error:$str");
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
				<th align=center>".$_SESSION['lang']['tanggalberlaku']."</th>
				<th align=center>".$_SESSION['lang']['transportir']."</th>
				<th align=center>".$_SESSION['lang']['kuantitas']."<br>(Kg)</th>
				<th align=center>".$_SESSION['lang']['namakapal']."<br><br>".$_SESSION['lang']['tarifkapal']."</th>
				<th align=center>".$_SESSION['lang']['namaponton']."<br><br>".$_SESSION['lang']['tarifponton']."</th>
				<th align=center>".$_SESSION['lang']['keperluan']."</th>
				<th align=center>".$_SESSION['lang']['kota']."</th>
				<th align=center>".$_SESSION['lang']['tandatangan']." I<br><br>".$_SESSION['lang']['tandatangan']." II</th>
				<th align=center colspan=5>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
		$no = 0;
		$str = "select * from ".$dbname.".".$table." where nokontrak='".$nokontrak."' and jenis='".$jenis."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent style=vertical-align:top>";
				// $tab.="<td align=center>".$no."</td>";
				$tab.="<td valign=top>".$bar['nokontrak']."</td>";
				$tab.="<td valign=top>".$nmpt[$bar['kodept']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalkontrak'])."</td>";
				$tab.="<td valign=top>".$nmcustsomer[$bar['kodecustomer']]."</td>";
				$tab.="<td valign=top>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td valign=top>".$bar['nospk']."</td>";
				$tab.="<td valign=top>".$namajenis[$bar['jenis']]."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggalberlaku'])."</td>";
				$tab.="<td valign=top>".$nmtransportir[$bar['transportir']]."</td>";
				$tab.="<td  valign=top align=right>".number_format($bar['kuantitas'])."</td>";
				
				$tab.="<td valign=top>".$nmkapalponton[$bar['namakapal']]."<br><br>".number_format($bar['tarifkapal'],2)."</td>";
				$tab.="<td valign=top>".$nmkapalponton[$bar['namaponton']]."<br><br>".number_format($bar['tarifponton'],2)."</td>";
				
				$tab.="<td valign=top>".$bar['keperluan']."</td>";
				$tab.="<td valign=top>".$bar['kota']."</td>";
				
				$tab.="<td>".$nmkaryawan[$bar['tandatangan']]."<br><br>".$nmkaryawan[$bar['tandatangan2']]."</td>";

			
			
			
				//$tab.="<td align=center>";
				if($bar['posting']==0){
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['kodecustomer']."','".$bar['kodebarang']."','".$bar['nospk']."','".$bar['jenis']."','".tanggalnormal($bar['tanggal'])."','".$bar['transportir']."','".$bar['kuantitas']."','".$bar['tarifkapal']."','".$bar['tarifponton']."','".$bar['namakapal']."','".$bar['tandatangan']."','".$bar['namaponton']."','".$bar['rupiah']."','".$bar['kota']."','".$bar['keperluan']."','".$bar['tandatangan2']."','".tanggalnormal($bar['tanggalberlaku'])."');\"></td>";
					 
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"delet('".$bar['nospk']."','".$bar['jenis']."');\"></td>";		
						
					$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";							
				} else{
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
		
				$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['nospk']."' onclick=\"printpdf('".$bar['nokontrak']."','".$bar['nospk']."','".$bar['jenis']."','".$table."','".$file."');\"></td>";	
				
				$tab.="<td align=center width=25px><img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$bar['nospk']."','SALES_".$bar['jenis']."') title=Upload>";
				$tab.="</td>";
            $tab.="</tr>";
        }
		$tab.="</table>";
		
			
			
        // }
		// $tab.="</table>";
		echo $tab;
	break;

    default:
	break;
}




?>
