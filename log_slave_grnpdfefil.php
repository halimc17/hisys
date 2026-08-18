<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$pages = checkPostGet('page','');
$urlefil=checkPostGet('urlefil','0');
##PARAMETER
$unit=checkPostGet('unit','');
$nopo=checkPostGet('nopo','');
$subunit=checkPostGet('subunit','');
$subunitdt=checkPostGet('subunitdt','');
$kegiatan=checkPostGet('kegiatan','');
$notransaksi=checkPostGet('notransaksi','');
$penerima=checkPostGet('penerima','');
$tanggal=checkPostGet('tanggal','');
$tanggalselesai=checkPostGet('tanggalselesai','');
$keterangan=checkPostGet('keterangan','');

##SEARCH
$scnopo=checkPostGet('scnopo','');
$scnotransaksi=checkPostGet('scnotransaksi','');
$sctanggal=checkPostGet('sctanggal','');


$str="select * from ".$dbname.".setup_kegiatan";
// exit("warning:".$str);
$res=fetchdata($str);
foreach($res as $bar){
	$klkegiatan[$bar['kodekegiatan']]=$bar['kelompok'];
}

switch($method){
	
	#= sumber transaksi ba kontrak jasa => pengadaaan->transaksi->ba kontrak jasa
	case'previewbapdf':
		$tab="<style>
			@page {
				margin-top: 10px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 30px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			footer {
				position: fixed; 
				bottom: -10px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";
		
		$cellpadding=1;
		$cellspacing=0;
		$sizefont='10';
		$border='1';
		
		$str="select * from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$nokontrak=$res[0]['nokontrak'];
		
		##HEADER
		$str="select * from ".$dbname.".log_kontrakjasa where notransaksi='".$nokontrak."'";
		$res=fetchdata($str);
		$notransaksiinduk=$res[0]['notransaksiinduk'];
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$tanggalkontrak=$res[0]['tanggal'];
		$deskripsi=$res[0]['deskripsi'];
		$supplier=$res[0]['supplierid'];
		$tgldari=$res[0]['tanggaldari'];
		$tglsampai=$res[0]['tanggalsampai'];
		$spesifikasi=$res[0]['spesifikasi'];
		$uangmuka=$res[0]['uangmuka'];
		$retensipersen=$res[0]['retensipersen'];
		$retensinilai=$res[0]['retensinilai'];
		$posting=$res[0]['posting'];
		$pembuat=$res[0]['postingby'];
		$exppt=explode(',',$pt);
		

		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']."</td>
				<td>:</td>
				<td>".$nokontrak."<input type='hidden' id='vnokontrak' value='".$nokontrak."'></td>
			</tr>
			<tr>
				<td style='min-width:115px'>No. ".$_SESSION['lang']['kontrak']." Induk</td>
				<td>:</td>
				<td>".$notransaksiinduk."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['pt']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".getNamaOrg($pt)."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['unit']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".getNamaOrg($unit)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']." Kontrak</td>
				<td>:</td>
				<td>".tanggalnormal($tanggalkontrak)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['deskripsi']."</td>
				<td>:</td>
				<td>".$deskripsi."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['koderekanan']."</td>
				<td>:</td>
				<td>".getNamaSupplier($supplier)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggalmulai']."</td>
				<td>:</td>
				<td>".tanggalnormal($tgldari)." s/d ".tanggalnormal($tglsampai)."</td>
			</tr>
			<tr>
				<td style='vertical-align:top'>".$_SESSION['lang']['spesifikasi']." ".$_SESSION['lang']['pekerjaan']."</td>
				<td style='vertical-align:top'>:</td>
				<td>".nl2br($spesifikasi)."</td>
			</tr>
			</table>";
		
		$tab.="<br><br>";
		
		$tab.="<table style='font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
		$tab.="<tr><td>".$_SESSION['lang']['noberitaacara']."</td><td>:</td><td>".$notransaksi."</td></tr>";
		$tab.="</table>";
		$tab.="<br>";
		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=".$border.">
			
			<thead><tr class=rowheader style=text-align:center>
				<td>".$_SESSION['lang']['nourut']."</td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>Item</td>
				<td>".$_SESSION['lang']['satuan']."</td>
				<td>Rp / ".$_SESSION['lang']['satuan']."</td>
				<td>".$_SESSION['lang']['jumlah']."</td>
				<td>".$_SESSION['lang']['jumlahrealisasi']."</td>
				<td>".$_SESSION['lang']['subunit']."</td>
				<td>Blok / Mesin PKS / Kend / AB</td>
				<td>".$_SESSION['lang']['kegiatan']."</td>
				<td>".$_SESSION['lang']['keterangan']."</td>
			</tr></thead>
			<tbody id='listdt'>";
			if($notransaksi==''){
				$tab.="<tr class=rowcontent><td style='text-align:center' colspan=1>".$_SESSION['lang']['datanotfound']."</td></tr>";
			}else{
				$str="select * from ".$dbname.".log_bakontrakjasa where notransaksi='".$notransaksi."' order by notransaksi asc";
				$res=fetchdata($str);
				if(count($res) > 0){
					$no=0;
					foreach($res as $val){
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td align=right>".$no."</td>";
						$tab.="<td style='min-width:70px'>".tanggalnormal($val['tanggal'])."</td>";
						$tab.="<td>".tipektrkjasa($val['noakun'])."</td>";
						$tab.="<td>".$val['kegiatan']."</td>";
						$tab.="<td style='text-align:center'>".$val['satuan']."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['rpsatuan'],2)."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['kuantitas'],2)."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['jumlah'],2)."</td>";
						// $tab.="<td style='text-align:left'>".getNamaOrg($val['subunit'])."</td>";
						
						#= nama subunit
						$strdt="select * from ".$dbname.".organisasi where kodeorganisasi in ('".$val['subunit']."','".$val['subunitdt']."')";
						$resdt=fetchdata($strdt);
						foreach($resdt as $bardt){
							$namaorganisasi[$bardt['kodeorganisasi']]=$bardt['namaorganisasi'];
						}
						
						#= nama subunit
						$strdt="select * from ".$dbname.".setup_kegiatan where kodekegiatan in ('".$val['kodekegiatan']."')";
						$resdt=fetchdata($strdt);
						foreach($resdt as $bardt){
							$namakegiatan[$bardt['kodekegiatan']]=$bardt['namakegiatan'];
						}
						
						$tab.="<td style='text-align:left'>".$val['subunit']." - ".$namaorganisasi[$val['subunit']]."</td>";
						$tab.="<td style='text-align:left'>".$val['subunitdt']." - ".$namaorganisasi[$val['subunitdt']]."</td>";
						$tab.="<td style='text-align:left'>".$val['kodekegiatan']." - ".$namakegiatan[$val['kodekegiatan']]."</td>";
						$tab.="<td style='text-align:left'>".$val['keterangan']."</td>";
						$tab.="</tr>";
						@$ttlkuantitas+=$val['kuantitas'];
						$total+=$val['jumlah'];
					}
					
					$tab.="<tr class='rowcontent' style='font-weight:bold'>";
					$tab.="<td colspan=6 style='text-align:right'>T O T A L</td>";
					$tab.="<td style='text-align:right'>".hidezerodecimal($ttlkuantitas,2)."</td>";
					$tab.="<td style='text-align:right'>".hidezerodecimal($total,2)."</td>";
					
					$tab.="<td colspan=4></td>";			
					
					$tab.="</tr>";
				}else{
					$tab.="<tr class='rowcontent'>";
						$tab.="<td colspan=12 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>";		
					$tab.="</tr>";
				}
			}
			$tab.="</tbody>
		</table><br><br><br>";

		#Approval
		$stra="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level=1";
		$res=fetchdata($stra);
		$aprlvl1=$res[0]['level'];   
		$aprid1=$res[0]['karyawanid'];    
		$tgl1=$res[0]['tanggal'];    

		$stra="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' and level=2";
		$res=fetchdata($stra);
		$aprlvl2=$res[0]['level'];   
		$aprid2=$res[0]['karyawanid'];  
		$tgl2=$res[0]['tanggal'];     
 
		$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$tab.="<br><br>";
		$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";
		$tab.="<tr>";
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>"; 
				
				$tab.="<tr align=center><td>Dibuat</td></tr>";  
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td><b>".$nmkar[$pembuat]." </b></td>";   
				$tab.="</tr>";
				$tab.="<tr align=center><td> ".tanggalnormal($tanggalkontrak)."</td></tr>";  
			$tab.="</table> ";
		$tab.="</td> "; 
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";  
				$tab.="<tr align=center><td>Persetujuan 1</td></tr>";    
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td><b>".$nmkar[$aprid1]." </b></td>";   
				$tab.="</tr>";
				$tab.="<tr align=center><td> ".tanggalnormal(substr($tgl1,0,10))." </td></tr>";   
			$tab.="</table> ";
		$tab.="</td> "; 
		$tab.="<td>";
			$tab.="<table style='width:100%;font-size:".($sizefont)."px' cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>"; 
				$tab.="<tr align=center><td>Persetujuan 2</td></tr>";   
				for ($i=0; $i < 15; $i++) { 
					$tab.="<tr>"; 
					$tab.="<td> </td>";   
					$tab.="</tr>"; 
				} 
				$tab.="<tr align=center>"; 
				$tab.="<td> <b>".$nmkar[$aprid2]." </b></td>";  
				$tab.="<tr align=center><td> ".tanggalnormal(substr($tgl2,0,10))." </td></tr>";   
			$tab.="</table> ";
		$tab.="</td></tr>";
		$tab.="</table> ";
		$tab.="<br><br><br>";
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("Print_BAST_".$nobast,array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}	
		
		
	break;	
	
	case'previewpdfgrba':
			
		$tab="";
		
		$str="select a.*,b.* from ".$dbname.".log_noninventory a 
				left join ".$dbname.".log_noninventorydt b on a.notransaksi=b.notransaksi where a.notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$jlhsat[$val['notransaksi']] += $val['jumlah'];
		}
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		$tanggalselesai=$res[0]['tanggalselesai'];
		$keterangan=$res[0]['keterangan'];
		$kodebarang=$res[0]['kodebarang'];
		$satuan=$res[0]['satuan'];
		$tipe =$res[0]['tipe '];
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optalamat=makeOption($dbname,'organisasi','kodeorganisasi,alamat',"kodeorganisasi='".$pt."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
		$optpurchaser=makeOption($dbname,'log_poht','nopo,purchaser',"nopo='".$nopo."'");
		
		$alamat=$optalamat[$pt];
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
		$purchaser=$optpurchaser[$nopo];
	
		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=left width=55px><img src='images/ksp.jpg'  class='zImgOffBtn' style='width:50px;height:50px'></td>
				<td align=center style='font-weight:bold;font-size:24px'>".$pt."</td>
			</tr>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-size:12px' colspan=2>Alamat : ".$alamat."</td>
			</tr>
			<br>
			<tr>
				<td align=center style='font-weight:bold;padding-top:19px;font-size:19px' colspan=2><u>BERITA ACARA  PENYELESAIAN PEKERJAAN</u></td>
			</tr>
			<tr>
				<td align=center style='font-size:15px' colspan=2>No : ".$notransaksi."</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;text-align: justify;' width=100%>
			<tr>
				<td>Pada hari ini ".hari($tanggalselesai)." Tanggal ".kekata(substr($tanggalselesai,8,2))." Bulan ".numToMonth(substr($tanggalselesai,5,2),'I','long')." Tahun ".kekata(substr($tanggalselesai,0,4))." telah dicek dan dioperasikan ".$optbarang[$kodebarang]." dengan spesifikasi seperti dibawah ini :<br><br></td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:15px;padding-left:100px;padding-right:100px;' width=100%>
			<tr>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td style='width:100px'>1. Jenis Pekerjaan</td>
						<td>=</td>
						<td>".$optbarang[$kodebarang]."</td>
					</tr>
					<tr>
						<td>2. ".$_SESSION['lang']['jumlah']."</td>
						<td>=</td>
						<td>".$jlhsat[$notransaksi]." ".$satuan."</td>
					</tr>
					<tr>
						<td>3. ".$_SESSION['lang']['kontraktor']."</td>
						<td>=</td>
						<td>".$supplier."</td>
					</tr>
					<tr>
						<td>4. Service Order</td>
						<td>=</td>
						<td>".$nopo."</td>
					</tr>
					<tr>
						<td>5. Selesai Pekerjaan</td>
						<td>=</td>
						<td>".tglnmblnhr($tanggalselesai,'I','long')."</td>
					</tr>
					<tr>
						<td>6. Keterangan</td>
						<td>=</td>
						<td>".$keterangan."</td>
					</tr>
					<br>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:15px' cellspacing=0>
			<tr>
				<td>Dengan data ini maka pembayaran  dapat dilaksanakan oleh Finance ke Kontraktor ".$supplier.".</td>
			</tr>
			<tr>
				<td><p>Demikian Berita Acara Penyelesaian Pekerjaan ini dibuat agar dipergunakan sebagai mana mestinya.</p><br><br></td>
			</tr>
			</table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:13px'>
			<tr style='text-align:center'>
				<td>Dibuat Oleh </td>
				<td>Diperiksa</td>
				<td>Disetujui Oleh</td>
			</tr>
			<tr>
				<td height=100px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td><b>".getNamaKaryawan($purchaser)."</b></td>
				<td><b>".getNamaKaryawan($penerima)."</b></td>
				<td><b>".getNamaKaryawan($postedby)."</b></td>
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($purchaser)."</td>
				<td>".getJabatanKaryawan($penerima)."</td>
				<td>".getJabatanKaryawan($postedby)."</td>
			</tr>
		</table>";
		
		
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		// $dompdf->stream("Print BA Penerimaan Barang", array("Attachment" => false));

		if($urlefil=='0'){
			$dompdf->stream("Print BA Penerimaan Barang", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
		
	break;
	
	
	case'previewpdfgr':
		$tab="";
	
		$str="select pt,unit,penerima,tanggal,nopo,supplierid,postedby from ".$dbname.".log_noninventory where notransaksi='".$notransaksi."'";
			
		$res=fetchdata($str);
		$pt=$res[0]['pt'];
		$unit=$res[0]['unit'];
		$penerima=$res[0]['penerima'];
		$tanggal=tanggalnormal($res[0]['tanggal']);
		$nopo=$res[0]['nopo'];
		$supplier=$res[0]['supplierid'];
		$postedby=$res[0]['postedby'];
		
		$optpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$pt."'");
		$optunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$unit."'");
		$optsupplier=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$optpurchaser=makeOption($dbname,'log_poht','nopo,purchaser',"nopo='".$nopo."'");
		
		$pt=$optpt[$pt];
		$unit=$optunit[$unit];
		$supplier=$optsupplier[$supplier];
		$purchaser=$optpurchaser[$nopo];

		$tab.="<table cellspacing=0 border=0 width=100% align=center>
			<tr>
				<td align=center style='border-bottom:0.1px solid #000;font-weight:bold'>BUKTI PENERIMAAN BARANG</td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 style='font-size:12px;' width=100%>
			<tr>
				<td width=60% style='font-weight:bold'>".$pt."</td>
				<td width=40% rowspan=2 style='vertical-align:bottom'>
				<table>
					<tr>
						<td>No. Transaksi</td>
						<td>:</td>
						<td>".$notransaksi."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['tanggal']."</td>
						<td>:</td>
						<td>".$tanggal."</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['nopo']."</td>
						<td>:</td>
						<td>".$nopo."</td>
					</tr>
				</table>
				</td>
			</tr>
			<tr>
				<td>
				<table>
					<tr>
						<td>Bisnis Unit</td>
						<td>:</td>
						<td>".$unit."</td>
					</tr>
					<tr>
						<td>Diterima Dari</td>
						<td>:</td>
						<td>".$supplier."</td>
					</tr>
				</table>
				</td>
			</tr>
		</table>";
		
		$tab.="<table style='width:100%;font-size:12px' cellpadding=3 cellspacing=0>
			<tr style='font-weight:bold'>
				<td align=center style='border:0.1px solid #000'>".$_SESSION['lang']['nourut']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['kodebarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['namabarang']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['satuan']."</td>
				<td align=right style='border:0.1px solid #000'>".$_SESSION['lang']['jumlah']."</td>
				<td style='border:0.1px solid #000'>".$_SESSION['lang']['keterangan']."</td>
			</tr>";
			
		$str="select * from ".$dbname.".log_noninventorydt where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		$no=0;
		foreach($res as $val){
			$no++;
			$optbarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
			
			##SUDAH DITERIMAKAN
			$sudahditerima=0;
			$strx="select sum(jumlah) as jumlah from ".$dbname.".log_noninventorydt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."' and notransaksi!='".$notransaksi."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$sudahditerima+=$valx['jumlah'];				
			}
			
			##KUANTITAS PO/SO
			$jumlahpesan=0;
			$strx="select jumlahpesan from ".$dbname.".log_podt where nopo='".$nopo."' and kodebarang='".$val['kodebarang']."'";
			$resx=fetchdata($strx);
			foreach($resx as $valx){
				$jumlahpesan=$valx['jumlahpesan'];				
			}
			
			$tab.="<tr>";
			$tab.="<td align=center style='border-left:0.1px solid #000'>".$no."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['kodebarang']."</td>";
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$optbarang[$val['kodebarang']]."</td>";
			
			$tab.="<td align=left style='border-left:0.1px solid #000'>".$val['satuan']."</td>";
			$tab.="<td align=right style='border-left:0.1px solid #000'>".hidezerodecimal($val['jumlah'],3)."</td>";
			/*if($urlefil=='0'){
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'>".getkegiatan(@$val['subunit'],@$val['subunitdt'],@$val['kodekegiatan'],2)."</td>";
			}else{*/
				$tab.="<td align=left style='border-left:0.1px solid #000;border-right:0.1px solid #000'></td>";
			//}
				// exit("Error:aaa");
			$tab.="</tr>";
		}
			
		$tab.="<tr><td colspan=6 style='border-top:0.1px solid #000'>&nbsp;</td></tr></table>";
		
		$tab.="<table width=100% cellpadding=0 cellspacing=0 style='font-size:12px'>
			<tr style='text-align:center'>
				<td>Administrasi Pembelian</td>
				<td>Diperiksa Oleh</td>
				<td>Diketahui Oleh</td>
			</tr>
			<tr>
				<td height=25px colspan=3>&nbsp;</td>
			</tr>
			<tr style='text-align:center;text-decoration:underline;'>
				<td>".getNamaKaryawan($purchaser)."</td>
				<td>".getNamaKaryawan($penerima)."</td>
				<td>".getNamaKaryawan($postedby)."</td>
			</tr>
			<tr style='text-align:center;'>
				<td>".getJabatanKaryawan($purchaser)."</td>
				<td>".getJabatanKaryawan($penerima)."</td>
				<td>".getJabatanKaryawan($postedby)."</td>
			</tr>
		</table>";
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		
		## Print Out
		if($urlefil=='0'){
			$dompdf->stream("Print RFQ", array("Attachment" => false));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}
	break;

}


?>