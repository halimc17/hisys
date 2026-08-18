<?php
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
	require_once('pmn_spk_nospk_slave.php');
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}

require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$param          = $_POST;if(count($param)==0){$param = $_GET;}
$urlefil=checkPostGet('urlefil','0');
$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));	
$unit = checkPostGet('unit','');
$tanggaltbs1 = tanggalsystemn(checkPostGet('tanggaltbs1',''));	
$tanggaltbs2 = tanggalsystemn(checkPostGet('tanggaltbs2',''));	
$keteranganht = checkPostGet('keteranganht','');
$divisi = checkPostGet('divisi','');
$noafiliasi = checkPostGet('noafiliasi','');
$tanggalspb = tanggalsystemn(checkPostGet('tanggalspb',''));	
// $tanggalpks = tanggalsystemn(checkPostGet('tanggalpks',''));	
$tanggalpks = checkPostGet('tanggalpks','');
$nospb = checkPostGet('nospb','');
$bjr = checkPostGet('bjr','');
$tahuntanam = checkPostGet('tahuntanam','');
$notiket = checkPostGet('notiket','');
$kodeblok = checkPostGet('kodeblok','');
$kgbruto = checkPostGet('kgbruto','');
$kgpotongan = checkPostGet('kgpotongan','');
$kgnetto = checkPostGet('kgnetto','');
$rpkg = checkPostGet('rpkg','');
$totalrp = checkPostGet('totalrp','');
$persenppn = checkPostGet('persenppn','');
$persenpph = checkPostGet('persenpph','');
$persenpph = checkPostGet('persenpph','');
$rp_pph = checkPostGet('rp_pph','');
$rp_ppn = checkPostGet('rp_ppn','');
$rpgrandtotal = checkPostGet('rpgrandtotal','');

$maxaproval	= checkPostGet('maxaproval','');
$persetujuan	= checkPostGet('persetujuan','');
$hal	= checkPostGet('page','');
$kdunit	= checkPostGet('kdunit','');
$type=checkPostGet('type','');
$unitinv=checkPostGet('unitinv','');
// exit('error'.$type); 
$dir='fileupload/tbs_beli';

$tanggalmulaisch=checkPostGet('tanggalmulaisch','');
$tanggalselesaisch=checkPostGet('tanggalselesaisch','');

if($tanggalmulaisch==''){
	$tanggalmulaisch='';
}else{
	$tanggalmulaisch = tanggalsystemn(checkPostGet('tanggalmulaisch',''));	
}

if($tanggalselesaisch==''){
	$tanggalselesaisch='';
}else{
	$tanggalselesaisch=tanggalsystemn(checkPostGet('tanggalselesaisch',''));
}
$notransaksisch=checkPostGet('notransaksisch','');
$kodetangkisch=checkPostGet('kodetangkisch','');

$table='kebun_tbsexternal';
// $tableafiliasi='kebun_tbsafiliasi';

$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";




$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi'); 






#= array nama kud
$str = "select * from ".$dbname.".kebun_5namakud where status=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   @$arrsupplier[$bar['afdeling']]=$bar['kodesupplier'];
   $kodeunit[$bar['afdeling']]=$bar['kodeunit'];
}

#= array kodesupplier
$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1  order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
	$kodesupplier[$bar['kodept']]=$bar['supplierid'];
}
	

#= ambil daftar unit didalam pt bentukan array
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}


#= ambil daftar kantor RO didalam pt bentukan array
$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KANWIL' ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$kodero[$bar['induk']]=$bar['kodeorganisasi'];
}

$hargaupafiliasi=100;

// exit("Error:".$method);
switch ($method) {
	
		
	case'getsupplier':
				
		// $optsupp=$optorginv= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		 
		$str = "SELECT * FROM " . $dbname . ".kebun_5namakud WHERE afdeling like '".$unit."%' and status=1";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optsupp.="<option value=" . $bar['kodesupplier'] . ">[".$bar['kodesupplier']."] - " . $nmsupplier[$bar['kodesupplier']] . "</option>";
			$optorginv.="<option value=" . $bar['kodeunit'] . ">[".$bar['kodeunit']."] - " . $nmpt[$bar['kodeunit']] . "</option>";
		}
 
		echo $optsupp."####".$optorginv;
	break;
	
	case'pdftimbangan':
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
			
		$str = "select * from ".$dbname.".".$table."  
			where notransaksi='".$notransaksi."' ";
			// echo $str;exit();
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			
			$divisi=$bar['divisi'];
			$unit=$bar['unit'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			
		#= ambil data kodetimbangan
		$str = "select * from ".$dbname.".log_5suptimbangan  where supplierid='".$divisi."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrkodetimbangan[$bar['kodetimbangan']]=$bar['kodetimbangan'];
		}

		
		
		$cellpadding=1;	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:16px'>";
		$tab.="<tr>";
			$tab.="<td align=center><b>Data Timbangan</td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	
		$tab.="<br>";	
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		
		$tab.="<tr>";
			$tab.="<td align=left>".$_SESSION['lang']['pabrik']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".$nmorg[$unit]." </td>";

			$tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
				$tab.="<td align=left>".$nmsupplier[$divisi]."</td>";
			
		$tab.="</tr>";	
	
			
		$tab.="<tr>";		
			$tab.="<td align=left>".$_SESSION['lang']['periode']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	
		
		$tab.="<br>";	
		
		$cellpadding=0;
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0.5 style='font-size:12px'>";
		$tab.="<tr bgcolor=lightgray>";
			$tab.="<td style='text-align:center;width:10px;'><b>".$_SESSION['lang']['nourut']."</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['noTiket']."</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['nospb']."</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['tanggal']."<br>PKS</td>"; 
			$tab.="<td style='text-align:center;'><b>Bruto</td>"; 
			$tab.="<td style='text-align:center;'><b>Potongan</td>"; 
			$tab.="<td style='text-align:center;'><b>Netto</td>"; 
		$tab.="</tr>";
	
		$str = "select * from ".$dbname.".pabrik_timbangan_vw  where 
		millcode='".$unit."' and kodebarang='40000003'
		and tanggal between '".$tanggaltbs1."' and '".$tanggaltbs2."' and intex=0 
		and kodecustomer in ('".implode("','",$arrkodetimbangan)."')";
				
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			@$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notiket']."</td>";
				$tab.="<td>".$bar['nospb']."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td align=right>".number_format($bar['beratbersih'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['kgpotsortasi'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['beratbersih']-$bar['kgpotsortasi'],2)."</td>";
			$tab.="</tr>";
			@$tberatbersih+=$bar['beratbersih'];
			@$tkgpotsortasi+=$bar['kgpotsortasi'];
        }
		#= total
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=left colspan=4><b>".$_SESSION['lang']['total']."</b></td>";
			$tab.="<td align=right><b>".number_format($tberatbersih,2)."</b></td>";
			$tab.="<td align=right><b>".number_format($tkgpotsortasi,2)."</b></td>";
			$tab.="<td align=right><b>".number_format($tberatbersih-$tkgpotsortasi,2)."</b></td>";
		$tab.="</tr>";
		
		
		$tab.="</table>";
		
		
			$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	break;
	
	case'pdf2':
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
			
		$str = "select d.namasupplier,c.namaorganisasi,c.alamat,c.induk,tanggaltbs1,tanggaltbs2  
			from ".$dbname.".kebun_tbsexternal a 
			left join ".$dbname.".organisasi c on concat('SD',substr(a.divisi,2,1),'E')=c.kodeorganisasi 
			left join ".$dbname.".log_5supplier d on a.supplier=d.supplierid  
			where notransaksi ='".$notransaksi."' group by a.supplier";
		
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$namasupplier=$bar['namasupplier'];
			$namaorgx=$bar['namaorganisasi'];
			$alamatorgx=$bar['alamat'];
			$indukorg=$bar['induk'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
		
		$cellpadding=1;	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		$tab.="<tr>";
		$tab.="<td align=left><b>".$nmpt[$indukorg]."</td>"; 	
		$tab.="</tr>";	
		$tab.="<tr>";
		$tab.="<td align=left><b>".$namaorgx."</td>"; 	
		$tab.="</tr>";	
		$tab.="<tr>";
		$tab.="<td align=left><b>".str_replace(',', '<br>', $alamatorgx)."</td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	

		$tab.="<br>";	

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		$tab.="<tr>";
			$tab.="<td align=center><b>PEMBAYARAN TBS PETANI ".$namasupplier." PERIODE TGL : ".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	

		
		// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		
		
		// $tab.="<tr>";
		// 	$tab.="<td align=left>".$_SESSION['lang']['no']."</td>"; 	
		// 	$tab.="<td align=left>:</td>"; 	
		// 	$tab.="<td align=left>".$notransaksi." </td>";
			
		// 	$tab.="<td align=left>".$_SESSION['lang']['tanggal']."</td>"; 	
		// 	$tab.="<td align=left>:</td>"; 	
		// 	$tab.="<td align=left>".tanggalnormal($tanggal)." </td>";
		// $tab.="</tr>";		
			
		
		// $tab.="<tr>";
		// 	$tab.="<td align=left>".$_SESSION['lang']['pabrik']."</td>"; 	
		// 	$tab.="<td align=left>:</td>"; 	
		// 	$tab.="<td align=left>".$nmorg[$unit]." </td>";

		// 	$tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>"; 	
		// 	$tab.="<td align=left>:</td>"; 	
		// 	$tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
		// $tab.="</tr>";	
	
			
		// $tab.="<tr>";		
		// 	$tab.="<td align=left>".$_SESSION['lang']['periode']."</td>"; 	
		// 	$tab.="<td align=left>:</td>"; 	
		// 	$tab.="<td align=left>".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>"; 	
		// $tab.="</tr>";	
		// $tab.="</table>";	
		
		$tab.="<br>";	
		
		$cellpadding=0;
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0.5 style='font-size:12px'>";
		$tab.="<tr bgcolor=lightgray>";
			$tab.="<td style='text-align:center;width:10px;'><b>".$_SESSION['lang']['nourut']."</td>"; 
			$tab.="<td style='text-align:center;'><b>GROUP HAMPARAN</td>"; 
			$tab.="<td style='text-align:center;'><b>TAHUN TANAM</td>"; 
			$tab.="<td style='text-align:center;'><b>JUMLAH HA</td>"; 
			$tab.="<td style='text-align:center;'><b>TBS QTY (KG NETTO)</td>"; 
			$tab.="<td style='text-align:center;'><b>HARGA TBS/KG (RP)</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['total']." (RP)</td>"; 
		$tab.="</tr>";
	
		
		
		
		$str = "select a.supplier,a.blok,c.namaorganisasi,b.tahuntanam,b.luasareaproduktif as luas,sum(a.kgnetto) as kgtbs,a.rpkg, sum(a.totalrp) as rptot 
			from ".$dbname.".kebun_tbsexternal a 
			left join ".$dbname.".setup_blok b on a.blok=b.kodeorg 
			left join ".$dbname.".organisasi c on a.blok=c.kodeorganisasi 
			where notransaksi ='".$notransaksi."' group by a.blok,a.supplier";
			// echo $str;exit();
		/*
			$str = "select 
			sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
			notransaksi,unit,divisi,tanggal,posting,
			tanggaltbs1,tanggaltbs2	 from ".$dbname.".".$table."  
			where ".$where."  group by notransaksi limit " . $offset . "," . $limit . " ";
		*/	
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$tkgnetto=0;
		$ttotalrp=0;
		$ttotluas=0;

        while ($bar = $res->fetch()){
			@$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".$bar['namaorganisasi']."</td>";
				$tab.="<td align=center>".$bar['tahuntanam']."</td>";
				$tab.="<td align=right>".number_format($bar['luas'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['kgtbs'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['rpkg'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['rptot'],2)."</td>";
			$tab.="</tr>";
			@$ttotluas+=$bar['luas'];
			@$tkgnetto+=$bar['kgtbs'];
			@$ttotalrp+=$bar['rptot'];
        }
		#= total
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=left colspan=3><b>".$_SESSION['lang']['total']."</b></td>";
			$tab.="<td align=right><b>".number_format($ttotluas,2)."</b></td>";
			$tab.="<td align=right><b>".number_format($tkgnetto,2)."</b></td>";
			$tab.="<td align=right></td>";
			$tab.="<td align=right><b>".number_format($ttotalrp,2)."</b></td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=left colspan=6><b>".$_SESSION['lang']['total']." Pembulatan</b></td>";
			$tab.="<td align=right><b>".number_format(floor($ttotalrp))."</b></td>";
		$tab.="</tr>";
		
		$tab.="</table>";
		
		// $tab.="<br>";	
		// $tab.="<br>";	

		// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		// $tab.="<tr>";
		// $tab.="<td align=center>DITERIMA OLEH</td>"; 	
		// $tab.="<td align=center>DISETUJUI OLEH</td>"; 
		// $tab.="<td align=center>DIKETAHUI OLEH</td>"; 
		// $tab.="<td align=center>DIBUAT OLEH</td>"; 
		// $tab.="</tr>";	
		// $tab.="<tr>";
		// $tab.="<td height=150px align=center><b>________________</td>"; 	
		// $tab.="<td height=150px align=center><b>________________</td>"; 	
		// $tab.="<td height=150px align=center><b>________________</td>"; 	
		// $tab.="<td height=150px align=center><b>________________</td>"; 	
		// $tab.="</tr>";	
		// $tab.="</table>";

		//exit($tab);
			$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	break;

	case'pdf':
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
		
		$idbank = makeOption($dbname,'log_5rekbank','supplierid,idbank');
		$namabank = makeOption($dbname,'log_5rekbank','supplierid,bank');
		$norekening = makeOption($dbname,'log_5rekbank','supplierid,rekening');
		$atasnama = makeOption($dbname,'log_5rekbank','supplierid,an');
		$str1="select kodebank, substr(namabank,5) as namabank2,namabank from ".$dbname.".keu_5daftarbank";
		$res1=fetchData($str1);
		foreach ($res1 as $bar1) {
			$nmBank[$bar1['kodebank']]=$bar1['namabank'];
		}

		$str = "select * from ".$dbname.".".$table."  
			where notransaksi='".$notransaksi."' ";
		
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$supplier=$bar['supplier'];
			$periodetbs=$bar['supplier'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$tanggal=$bar['tanggal'];
			$unit=$bar['unit'];
		
		$cellpadding=1;	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:16px'>";
		$tab.="<tr>";
			$tab.="<td align=center><b>Pembayaran TBS</td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	
		$tab.="<br>";	
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
		
		
		$tab.="<tr>";
			$tab.="<td align=left>".$_SESSION['lang']['nodok']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".$notransaksi." </td>";
			
			$tab.="<td align=left>".$_SESSION['lang']['tanggal']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".tanggalnormal($tanggal)." </td>";
		$tab.="</tr>";		
			
		
		$tab.="<tr>";
			$tab.="<td align=left>".$_SESSION['lang']['pabrik']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".$nmorg[$unit]." </td>";

			$tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>"; 	
			$tab.="<td align=left>:</td>"; 	
			$tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
		$tab.="</tr>";	
	
			
		$tab.="<tr>";		
			$tab.="<td align=left valign=top>".$_SESSION['lang']['periode']."</td>"; 	
			$tab.="<td align=left valign=top>:</td>"; 	
			$tab.="<td align=left valign=top>".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>"; 
			
			$tab.="<td align=left valign=top>".$_SESSION['lang']['norek']."</td>"; 	
			$tab.="<td align=left valign=top>:</td>"; 	
			// $tab.="<td align=left><b>".$nmBank[$idbank[$supplier]]."</b> ".$norekening[$supplier]." a/n ".$atasnama[$supplier]."</td>"; 	
			$tab.="<td align=left valign=top><b>".$namabank[$supplier]."</b> ".$norekening[$supplier]." a/n ".$atasnama[$supplier]."</td>"; 	
		$tab.="</tr>";	
		$tab.="</table>";	
		
		$tab.="<br>";	
		
		$cellpadding=0;
		$border=0.5;
		if ($type=='excel') {
			$border=1;
		}
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."' style='font-size:12px'>";
		$tab.="<tr bgcolor=lightgray>";
			$tab.="<td style='text-align:center;width:10px;'><b>".$_SESSION['lang']['nourut']."</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['tanggal']."<br>SPB</td>"; 
			$tab.="<td style='text-align:center;width:120px;'><b>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['pabrik']."</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['blok']."</td>"; 
			$tab.="<td style='text-align:center;'><b>Bruto</td>"; 
			$tab.="<td style='text-align:center;'><b>Potongan</td>"; 
			$tab.="<td style='text-align:center;'><b>Netto</td>"; 
			$tab.="<td style='text-align:center;'><b>Rp/Kg</td>"; 
			$tab.="<td style='text-align:center;'><b>".$_SESSION['lang']['total']."</td>"; 
		$tab.="</tr>";
	
		
		
		
		$str = "select * from ".$dbname.".".$table."  
			where notransaksi='".$notransaksi."' ";
			// echo $str;exit();
		/*
			$str = "select 
			sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
			notransaksi,unit,divisi,tanggal,posting,
			tanggaltbs1,tanggaltbs2	 from ".$dbname.".".$table."  
			where ".$where."  group by notransaksi limit " . $offset . "," . $limit . " ";
		*/	
		$no=$tkgnetto=$tkgbruto=$tkgpotongan=$ttotalrp=0;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			@$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=center>".tanggalnormal($bar['tanggalspb'],1)."</td>";
				$tab.="<td align=center>".tanggaljamnormal($bar['tanggalpks'])."</td>";
				$tab.="<td>".$bar['blok']."</td>";
				$tab.="<td align=right>".number_format($bar['kgbruto'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['kgpotongan'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['kgnetto'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['rpkg'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['totalrp'],2)."</td>";
			$tab.="</tr>";
			@$tkgnetto+=$bar['kgnetto'];
			@$tkgbruto+=$bar['kgbruto'];
			@$tkgpotongan+=$bar['kgpotongan'];
			@$ttotalrp+=$bar['totalrp'];
        }
		#= total
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=left colspan=4><b>".$_SESSION['lang']['total']."</b></td>";
			$tab.="<td align=right><b>".number_format($tkgbruto,2)."</b></td>";
			$tab.="<td align=right><b>".number_format($tkgpotongan,2)."</b></td>";
			$tab.="<td align=right><b>".number_format($tkgnetto,2)."</b></td>";
			$tab.="<td align=right></td>";
			$tab.="<td align=right><b>".number_format($ttotalrp,2)."</b></td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
			$tab.="<td align=left colspan=8><b>".$_SESSION['lang']['total']." Pembulatan</b></td>";
			$tab.="<td align=right><b>".number_format(floor($ttotalrp))."</b></td>";
		$tab.="</tr>";
		
		$tab.="</table>";
		
		if(count($_POST)>0){$param=$_POST;}else{$param=$_GET;}
		if($param['tampilan']=='PDF'){
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();

			$filepdf=$param['namafile'];
			if (file_exists($filepdf)){
				unlink($filepdf);
			}
			file_put_contents($filepdf, $dompdf->output());
		}elseif($type=='excel'){
			$tab.="<table border=1 class=sortable cellspacing=1>"; 
			$tab.="</table>Print Time:".date('YmdHis')."<br>By:".$_SESSION['empl']['name'];	
			$nop_="Pembayaran_TBS_Petani_".date('m-d-Y');
			echo $tab;
			// exit('error');
			if(strlen($tab)>0)
			{
				$gztralala = gzopen("tempExcel/".$nop_.".xls.gz", "w9");
				gzwrite($gztralala, $tab);
				gzclose($gztralala);
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls.gz';
					</script>"; 
			} 
		}else{
			$dompdf = new Dompdf();
			$dompdf->loadHtml($tab);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			if($urlefil=='0'){
				$dompdf->stream($table,array("Attachment"=>0));	
			}else{
				file_put_contents($urlefil, $dompdf->output());
			}
		}
	break;
	
	case'posting':
	
		
		#= data transaksi
		$str = "select 
		sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
		notransaksi,unit,divisi,tanggal,posting,supplier,tanggal,
		tanggaltbs1,tanggaltbs2,rounit,ropemilik	 from ".$dbname.".".$table."  
		where  notransaksi ='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$tanggal=$bar['tanggal'];
			$unit=$bar['kodeorginv'];
			$totalrp=floor($bar['totalrp']);
			// $totalrp=$bar['totalrp'];
			$supplier=$bar['supplier'];
			$rounit=$bar['rounit'];
			$ropemilik=$bar['ropemilik'];
		
		
		#= prepare jurnal
		#= cek sudah tutup buku / belum
		$str="select tutupbuku from ".$dbname.".setup_periodeakuntansi where 
			  periode='".substr($tanggal,0,7)."' and kodeorg='".$unit."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tutupbuku=$bar['tutupbuku'];
		if($tutupbuku==1){
			exit("Warning:Periode ini sudah ditutup");
		}
		
		$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplier."'");
		$namasupplier = $optsup[$supplier];
			
		#====notransaksi jurnal akun debet serta kredit dari parameter jurnal
		$kodejurnal="INVTB";
		$optInduk=makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$unit."'");
		// $whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$unit]."'";
		$whereNoindukph = "kodekelompok='".$kodejurnal."' and kodeorg='".$optInduk[$unit]."' and kodeunit='".$unit."' and periode='".substr($tanggal,0,7)."'";
		
		$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNoindukph);
		$noKon = fetchData($query);
		$tmpC = $noKon[0]['nokounter'];
		$tmpC++;
		$counterjurnal = addZero($tmpC,3);
		$nojurnal = str_replace("-","",$tanggal)."/".$unit."/".$kodejurnal."/".$counterjurnal;
		// exit("Error:$nojurnal");
		#akun debet serta krdit
		$query2 = selectQuery($dbname,'keu_5parameterjurnal','noakundebet,noakunkredit',"jurnalid='".$kodejurnal."' and aktif=1");
		$dtnoakun = fetchData($query2);
		
		#=== Transform Data ===
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		
		# Prep Header
		$dataRes['header'] = array(
			'nojurnal'=>$nojurnal,
			'kodejurnal'=>$kodejurnal,
			'tanggal'=>$tanggal,
			'tanggalentry'=>date('Ymd'),
			'posting'=>'0',
			'totaldebet'=>($totalrp),
			'totalkredit'=>($totalrp)*-1,
			'amountkoreksi'=>'0',
			'noreferensi'=>$notransaksi,
			'autojurnal'=>'1',
			'matauang'=>'IDR',
			'kurs'=>'1',
			'revisi'=>'0'
		);
		
		#= debet
		// exit("Error".$dtnoakun[0]['noakundebet']);
		$noUrut=1;
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggal,
			'nourut'=>$noUrut,
			'noakun'=>'6410103',
			'keterangan'=>'Penerimaan TBS unit '.$unit.' dari '.$namasupplier.' pada tanggal '.$tanggaltbs1.' s/d tanggal '.$tanggaltbs2,
			'jumlah'=>$totalrp,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unit,
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>$supplier,
			'noreferensi'=>$notransaksi,
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$notransaksi,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => '0000000001'
		);
		
		$noUrut++;
		$dataRes['detail'][] = array(
			'nojurnal'=>$nojurnal,
			'tanggal'=>$tanggal,
			'nourut'=>$noUrut,
			'noakun'=>$dtnoakun[0]['noakunkredit'],
			'keterangan'=>'Penerimaan TBS unit '.$unit.' dari '.$namasupplier.' pada tanggal '.$tanggaltbs1.' s/d tanggal '.$tanggaltbs2,
			'jumlah'=>($totalrp)*-1,
			'matauang'=>'IDR',
			'kurs'=>'1',
			'kodeorg'=>$unit,
			'kodekegiatan'=>'',
			'kodeasset'=>'',
			'kodebarang'=>'',
			'nik'=>'',
			'kodecustomer'=>'',
			'kodesupplier'=>$supplier,
			'noreferensi'=>$notransaksi,
			'noaruskas'=>'',
			'kodevhc'=>'',
			'nodok'=>$notransaksi,
			'kodeblok'=>'',
			'revisi'=>'0',
			'kodesegment' => '0000000001'
		);
		
	
		$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
		try {
			$owlPDO->exec($queryH);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		
		foreach($dataRes['detail'] as $key=>$dataDet) {
			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
			try {
				$owlPDO->exec($queryD);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				die();
			}
		}
		
		$queryJ = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$tmpC),$whereNoindukph);
		$errCounter = "";
		try{$owlPDO->exec($queryJ); }catch (PDOException $e) { $errCounter.= "Update Counter Parameter Jurnal Error :". $e->getMessage() ; }
			

		$str = "update ".$dbname.".".$table." set 
				posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'deleteht':
		$str = "delete from ".$dbname.".".$table." where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
		
	break;
	
	
	case'notransaksi':
		
		$tglterakhir = date('Y-m-t', strtotime($tanggal));
		$periode=substr($tanggal,0,7);

		
		$arrtanggal=rangeTanggalarr($tanggaltbs1,$tanggaltbs2);
		// echo"<pre>";
		// print_r($arrtanggal);
		// exit("Error:A");
		$texterror='';
		foreach($arrtanggal as $tglcek){
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".kebun_tbsexternal where divisi='".$divisi."' and unit='".$unit."' and tanggaltbs1='".$tglcek."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['jumlah']>0){
				$texterror.="sudah ada data ditanggal tbs ".tanggalnormal($tglcek)." dengan nomor transaksi ".$bar['notransaksi']."\n ";
			}
			
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".kebun_tbsexternal where divisi='".$divisi."' and unit='".$unit."' and tanggaltbs2='".$tglcek."'"; 
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			if($bar['jumlah']>0){
				$texterror.="sudah ada data ditanggal tbs ".tanggalnormal($tglcek)." dengan nomor transaksi ".$bar['notransaksi']."\n ";
			}
		}
		
		if($texterror!=''){
			echo $texterror;
			exit("Warning:Gagal Proses");
		}
		
		
	
		$notransaksi = generatenotransaksitbsexternal();	
		$noafiliasi='';
		
		echo $notransaksi."###".$noafiliasi;
	break;
	
	
	case 'postingData':
		$countApp = getCountApproval('PTBS', $kdunit);

		$tab = "";
		$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%; height:100%'>";

		if ($countApp == null) {
			$tab .= "<tr class=rowcontent>
	            		<td align=center valign=middle>
	            			<h3>Approval untuk pembelian TBS External unit ".$kdunit." masih belum didaftarkan<br> Silahkan hubungi Administrator</h3>
	            		</td>
	            	</tr>";
		} else {
			for($i=1; $i<=$countApp; $i++){
				$arrList = listApprove($i, 'PTBS', $kdunit);
				$optpersetujuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

				foreach($arrList as $key => $val){
					$optpersetujuan.="<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
				}
	            $tab .= "<tr class=rowcontent>
	            			<td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
	            			<td align=center>:</td>
	            			<td colspan=1>
	            				<select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select>
	            			</td>
	            		</tr>"; 
				
			}   

			$tab .= "<tr class=rowcontent>
						<td colspan=2></td>
						<td style='text-align:left'>
							<button class=mybutton onclick=\"simpanApproval('".$notransaksi."','".$countApp."','".$hal."');\">Simpan</button>
						</td>
					</tr>";
		}

		$tab .= "</table>";

		$str = "SELECT a.level, b.namakaryawan, a.status, left(a.tanggal, 10) as tanggal
				FROM ".$dbname.".approval_return a
				LEFT JOIN datakaryawan b
				ON a.karyawanid = b.karyawanid
				WHERE a.jenispersetujuan = 'PTBS'
				AND a.notransaksi = '".$notransaksi."'";
		$res = fetchData($str);

		if (count($res) > 1) {
			$tab .= "<br>";
			$tab .= "<fieldset><legend>History Approval</legend>";
			$tab .= "<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%; height:100%'>";
			$tab .= "<thead>
						<tr class=rowheader>
							<th>Persetujuan</th>
							<th>Level</th>
							<th>Status</th>
							<th>Tanggal</th>
						</tr>
					</thead>
					<tbody>";

			foreach ($res as $key => $val) {
				if ($val['status'] == 1) {
					$status = 'Disetujui';
				} else if ($val['status'] == 0) {
					$status = 'Waiting';
				} else if ($val['status'] == 3) {
					$status = 'Dikoreksi';
				} else if ($val['status'] == 2) {
					$status = 'Ditolak';
				}

				$tab .= "<tr class=rowcontent>
							<td>".$val['namakaryawan']."</td>
							<td>".$val['level']."</td>
							<td>".$status."</td>
							<td>".tanggalnormal($val['tanggal'])."</td>
						</tr>";
			}

			$tab .= "</tbody></table></fieldset>";
		}
		
		echo $tab;
	break;

	case 'cekapproval':
		$arrnama = array();
		$no = 0;
		$nomor = 0;

		$tab = "";
		$tab .= "<table cellpadding=5 cellspacing=1 border=0 class=sortable style='width:100%;'>";

		$str = "SELECT karyawanid, status 
				FROM ".$dbname.".approval
				WHERE notransaksi = '".$notransaksi."'";
		$res = fetchData($str);

		foreach ($res as $val) {
			$nomor = $no + 1;

			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['karyawanid']."'");
			$arrnama[$no]['nama'] = $optNmKar[$val['karyawanid']];

			if ($val['status'] == 0) {
				$stat = 'Waiting';
			} else if ($val['status'] == 1) {
				$stat = 'Disetujui';
			} else if ($val['status'] == 3) {
				$stat = 'Dikoreksi';
			} else if ($val['status'] == 2) {
				$stat = 'Ditolak';
			}

			$tab .= "<tr class=rowcontent>
						<td>Persetujuan ".$nomor." : ".$arrnama[$no]['nama']." (".$stat.")</td>
					</tr>";
			$no++;
		}

		$tab .= "</table>";
		
		echo $tab;
	break;
	
	case 'persetujuan':
		$listpersetujuan = $_POST['persetujuan'];
		for($i=1; $i<=count($listpersetujuan); $i++){
			if($_POST['persetujuan'][$i]==''){
				exit("Warning: Persetujuan ".$i." belum dipilih.");
			}
		}

		#= data transaksi
		$str = "SELECT unit,tanggal
				FROM ".$dbname.".".$table."  
				WHERE notransaksi = '".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$tanggal = $bar['tanggal'];
		$unit = $bar['unit'];
		
		#= cek sudah tutup buku / belum
		$str = "SELECT tutupbuku FROM ".$dbname.".setup_periodeakuntansi WHERE 
			  periode='".substr($tanggal,0,7)."' and kodeorg='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$tutupbuku=$bar['tutupbuku'];
		if($tutupbuku==1){
			exit("Warning:Periode ini sudah ditutup");
		}

		#= delete 1st untuk aprovalnya
        $delapp = "delete from ".$dbname.".approval where jenispersetujuan = 'PTBS' and notransaksi='".$notransaksi."'";	
        try {
			$owlPDO->exec($delapp);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	

        $str = "UPDATE ".$dbname.".kebun_tbsexternal set posting = 9, postingby = '".$_SESSION['standard']['userid']."'
                WHERE notransaksi = '".$notransaksi."'";
        try{
            $owlPDO->exec($str); 
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    
        for($i=1; $i<=$maxaproval; $i++){
            #= insert
            $str = "INSERT INTO ".$dbname.".approval
            		(notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                   	VALUES
                   	('".$notransaksi."','PTBS','".$i."','".$persetujuan[$i]."','0','','','0000-00-00 00:00:00')";
            try{
                $owlPDO->exec($str); 
            }catch(PDOException $e){
               	$str = "UPDATE ".$dbname.".kebun_tbsexternal set posting = 0
                		WHERE notransaksi = '".$notransaksi."'";
                try{
                    $owlPDO->exec($str); 
                }catch(PDOException $e){
                    echo " Gagal," . addslashes($e->getMessage());
                }
                echo " Gagal," . addslashes($e->getMessage());
            }
        }
		
		// $to = getUserEmail($persetujuan[1]);
        // $namakaryawan = getNamaKaryawan($_SESSION['standard']['userid']);
        // $subject = "[Notifikasi]Persetujuan untuk transaksi proses Pembayaran TBS External dengan nomor " . $notransaksi;
        // $body = "<html>
		// 	 <head>
		// 	 <body>
		// 	   <dd>Dengan Hormat,</dd><br>
		// 	   <br>
		// 	   Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  " . $namakaryawan . " mengajukan persetujuan untuk transaksi proses Pembayaran TBS External dengan nomor ".$notransaksi."
		// 	   <br>
		// 	   <br>
		// 	   Regards,<br>
		// 	   Owl-Plantation System.
		// 	 </body>
		// 	 </head>
		//    </html>
		//    ";
		// if ($to != '') {
		// 	$kirim = kirimEmail($to, '', $subject, $body);
		// } 
	break;

   	case'loaddata':


		$where=" unit in (".getorgdetail(2).")";
		// $where=" 1=1 ";
		// $where.=" and divisi like '%".$_SESSION['empl']['lokasitugas']."%'";
		if($tanggalselesaisch!='' and $tanggalmulaisch!=''){
			$where.=" and tanggal between '".$tanggalmulaisch."' and '".$tanggalselesaisch."'";
		}
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		$limit = 10;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
	// echo $where;
		
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where." group by notransaksi";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar = $res->fetch()){
            $jumrow = $bar['jumrow'];
		}
			
		$no = 0;
		$no=$maxdisplay;
		$str = "select 
			sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,
			notransaksi,unit,supplier,tanggal,posting,
			tanggaltbs1,tanggaltbs2,pemilik,noreferensi	 from ".$dbname.".".$table."  
			where ".$where." group by notransaksi order by tanggal desc,notransaksi desc  limit " . $offset . "," . $limit . " ";
			
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$pg = $page + 1;

			if ($bar['posting']==3) {
				$tab.="<tr class=rowcontent title=Koreksi style=background:orange>";
			} else {
				$tab.="<tr class=rowcontent>";
			}
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$bar['unit']."</td>";
				$tab.="<td>".$nmsupplier[$bar['supplier']]."</td>";
				$tab.="<td>".$bar['pemilik']."</td>";
				
				
				$tab.="<td>".tanggalnormal($bar['tanggaltbs1'])." s/d ".tanggalnormal($bar['tanggaltbs2'])."</td>";
				$tab.="<td align=right>".number_format($bar['kgnetto'],2)."</td>";
				$tab.="<td align=right>".number_format($bar['totalrp'],2)."</td>";
				$tab.="<td align=right>".number_format(floor($bar['totalrp']))."</td>";
				$tab.="<td>".$bar['noreferensi']."</td>";
				if($bar['posting']==0 || $bar['posting']==3){
				$tab.="<td align=center width=25px>";
					$tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\">";
					$tab.="</td><td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."');\"></td>";		
					if ($bar['posting']==0) {
						$tab.="<td align=center width=25px><img src=images/skyblue/submit.jpg class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"postingData('".$bar['notransaksi']."','".$bar['unit']."','".$pg."');\"></td>";
					} else {
						$tab.="<td align=center width=25px><img src=images/icons/04/16/08.png class=resicon class=zImgBtn height='30'  title='Koreksi' onclick=\"postingData('".$bar['notransaksi']."','".$bar['unit']."','".$pg."');\"></td>";
					}
				} else if($bar['posting']==9){
					$tab.="<td align=center width=25px></td>"; 
					$tab.="<td align=center width=25px><img src=images/zoom.png class=resicon caption='Cek Approval' onclick=\"cekapproval('".$bar['notransaksi']."');\"></td>";
				} else {
					$tab.="<td align=center width=25px></td>"; 
					$tab.="<td align=center width=25px></td>"; 
					$tab.="<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting'></td>";
				}
				$tab.="<td align=center width=25px><img src=images/excel.jpg class=resicon  caption='Excel'  title='Print Excel ".$bar['notransaksi']."' onclick=\"excel('".$bar['notransaksi']."');\"></td>";	
				$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."');\"></td>";	
				// $tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print Rekap ".$bar['notransaksi']."' onclick=\"pdf2('".$bar['notransaksi']."');\">";	
				$tab.="<td align=center width=25px><img src=images/upload-2-xxl.png class=resicon title='Upload File' onclick=\"showupload('".$bar['notransaksi']."');\">";	
				$tab.="</td>";
			$tab.="</tr>";
        }
		
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi";
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
            <tr><td colspan=17 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
	break;

	case'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload ".$notransaksi."</legend>
		<table border=0>
			<tr>
				<td>Upload</td>
				<td>:</td>
				<td colspan=6>
					<input type='file' name='upload' id='fileupload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td colspan=7>
					<button id=btnsubmit class=mybutton onclick=\"savefile('".$notransaksi."')\">Simpan</button>
				</td>
			</tr>
		</table>
		</fieldset>";
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		echo $tab;
	break;
	
	case'savefile':
		$fileupload = strtolower('.'.substr($_FILES['fileup']['name'],strripos($_FILES['fileup']['name'],'.')+1));
		$fileupload = $fileupload;

		/* $filesize=$_FILES['fileup']['size'];
		
		if($filesize>=500000)
		{
			exit("Warning : Besar ukuran file maksimal 500 KB. ");
		} */
		$notransaksi=str_replace("/","_", $notransaksi);
		$newfilename = $notransaksi."_".$_FILES['fileup']['name'];
		$filename = $newfilename."".$fileupload;
		$path = $dir."/".$newfilename;

		$file_tmpname = $_FILES['fileup']['tmp_name'];
		$cekq="select * from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$newfilename."' and  kriteriaefil='TBSKUD' and formaticon='".$fileupload."' and status='1' ";	
		$resq=fetchData($cekq);
		$dataq=count($resq);
		if ($dataq != 0) {
			exit("Warning: Nama file sudah ada mohon ganti nama file yang akan diupload");
		}
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		if (move_uploaded_file($file_tmpname,$path)) {
			$str="INSERT INTO ".$dbname.".`listfileupload` (`notransaksi`,`namafile`, `formaticon`,`kriteriaefil`,`status`, `createdby`,`createdtime`) 
				VALUES ('".$notransaksi."','".$newfilename."', '".$fileupload."','TBSKUD','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:m:s')."')";
			try{
				$owlPDO->exec($str);
				 
			}
			catch (PDOException $e) {
				print " Gagal !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}	
		
		echo $_SESSION['lang']['datatersimpan'];
		
	break;

	case 'loadfiles':
	
		$no = 0;
		$tab = "";
		$notransaksi=str_replace("/","_", $notransaksi);
		$str="select * from ".$dbname.".listfileupload where notransaksi like'".$notransaksi."' and kriteriaefil = 'TBSKUD' and status='1'";
		// exit("Error:".$str);
		$res=fetchData($str);
		if(empty($res)){
			$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			foreach($res as $key=>$val){
				$no++;
				$tab.="<tr class=rowcontent>
						<td style='text-align:center'>".$no."</td>";
				$icon=seticonfile($val['formaticon']);
				$tab.="<td style='text-align:center'>
						<a href='".$dir."/".$val['namafile']."' download><img src=".$icon." class=resicon></a>
					</td>";
				$nfile='';
				$nfile = $val['namafile'];
				$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewimage('".$dir."/".$nfile."','".$val['formaticon']."');\">".$nfile."</td>
					<td align=center>
						<a href='".$dir."/".$nfile."' download><img src=images/uploader/dwnld8.png class=resicon title='download'></a>&nbsp";
				$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
				$tab."	</td>
					</tr>";
			}
		}
		echo $tab;
		break;

	case'deletefile':
		$namafile = checkPostGet('namafile','');
		$str = "delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
		try {
			$owlPDO->exec($str);
			$pathx = $dir."/".$namafile;
			unlink($pathx);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	case'geteditht':
	
		
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
			$notransaksi=$bar['notransaksi'];
			$unit=$bar['unit'];
			$divisi=$bar['divisi'];
			$tanggal=$bar['tanggal'];
			$tanggaltbs1=$bar['tanggaltbs1'];
			$tanggaltbs2=$bar['tanggaltbs2'];
			$keteranganht=$bar['keteranganht'];

			$noafiliasi='';
		
		// exit("Error:$str");
		
		echo $notransaksi."###".$unit."###".$divisi
		."###".tanggalnormal($tanggal)."###".tanggalnormal($tanggaltbs1)."###".tanggalnormal($tanggaltbs2)
		."###".$keteranganht."###".$noafiliasi;
		// exit("Error:a");
	break;
	
	
	/********************** detail ***************************/
	
	case 'savedt':
		
		

		#= insert afiliasi jika
		if($notiket==''){
			exit("Warning:Nomor tiket timbang pabrik tidak terdaftar");
		}
		
		#= delete 1st
		$str = "delete from ".$dbname.".".$table." where 
			notransaksi='".$notransaksi."' and notiket='".$notiket."' and nospb='".$nospb."' and blok='".$kodeblok."'";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
		
		// exit('error '.$unitinv);
		$data = array(
			'notransaksi'=>$notransaksi,
			'unit'=>$unit,
			'tanggal'=>$tanggal,
			'divisi'=>$divisi,
			'tanggaltbs1'=>$tanggaltbs1,
			'tanggaltbs2'=>$tanggaltbs2,
			'keteranganht'=>$keteranganht,
			'tanggalspb'=>'',
			'tanggalpks'=>$tanggalpks,
			'nospb'=>$nospb,
			'notiket'=>$notiket,
			'blok'=>$kodeblok,
			'kgbruto'=>$kgbruto,
			'kgpotongan'=>$kgpotongan,
			'kgnetto'=>$kgnetto,
			'bjr'=>$bjr,
			'tahuntanam'=>$tahuntanam,
			'rpkg'=>$rpkg,
			'totalrp'=>$totalrp,
			'supplier'=>$divisi,
			'persenppn'=>$persenppn,
			'persenpph'=>$persenpph,
			'nilaipph'=>$rp_pph,
			'nilaippn'=>$rp_ppn,
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d H:i'),
			'updateby' => $_SESSION['standard']['userid'],
			'pemilik' =>'',
			'kodeorginv'=>$unitinv
		);
		
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 	
		// exit("Error:$str");

		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}

	break;
	
	case'loaddatadt':
		
		$persenppn=0;
		$persenpph=0;
		$persenppn=$param['persenppn'];
		$persenpph=$param['persenpph'];
		#= data lama
		$datatransaksi=0;
		$str = "select count(*) as jumrow from ".$dbname.".".$table."  where notransaksi='".$notransaksi."'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
			$datatransaksi=$bar['jumrow'];
		
		// echo $datatransaksi;
		if($datatransaksi==0){
			#= harga tbs
		} else {
			#= data lama
			$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."' ";
			$res=fetchdata($str);
			foreach($res as $bar){
				@$datarpkg[$bar['nospb']][$bar['blok']]=$bar['rpkg'];
				@$datatotalrp[$bar['nospb']][$bar['blok']]=$bar['totalrp'];
				@$ttotalrp+=$bar['totalrp'];
			}
		}
		
		#= ambil data kodetimbangan
		// $str = "select * from ".$dbname.".log_5suptimbangan  where supplierid='".$divisi."' ";
		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$arrkodetimbangan[$bar['supplierid']]=$bar['supplierid'];
		// }
		
		// #= cek data timbangan
		// @$ckodetimbangan=count($arrkodetimbangan);
		// if($ckodetimbangan<1){
		// 	exit("Warning:Data timbangan belum di sync dengan master data supplier");
		// }
	
		$nomax=0;
		// $str = "select * from ".$dbname.".pabrik_timbangan_vw  where 
		// 		kodeorg='".$unit."' and kodebarang='40000003'
		// 		and tanggal between '".$tanggaltbs1."' and '".$tanggaltbs2."' and intex=0 
		// 		";

		$str = "SELECT a.*, b.kgpotsortasi from ".$dbname.".kebun_spb_detail_vw a LEFT JOIN  ".$dbname.".pabrik_timbangan b ON a.nospb=b.nospb where 
				a.blok LIKE '%".$unit."%' and a.tanggal between '".$tanggaltbs1."' and '".$tanggaltbs2."'";
				
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			
			$arrblok[$bar['nospb']][$bar['blok']]=$bar['blok'];
			$arrnospbex[$bar['nospb']]=$bar['nospb'];
			$nospb_tiket[$bar['nospb']][$bar['blok']]=$bar['nospb'];
			 //dibuat rumus karna angka dari timbangan tidak tepat dikarenakan bjr bisa di-editht
			// @$bjrpk[$bar['blok']]=$bar['beratbersih']/$bar['jjg'];
			$tahuntanampk[$bar['nospb']][$bar['blok']]=$bar['tahuntanamperblok'];
			$bjrpk[$bar['nospb']][$bar['blok']]=$bar['bjr'];
			$tglpk[$bar['nospb']][$bar['blok']]=$bar['tanggal'];
			$nokendaraan[$bar['nospb']][$bar['blok']]=$bar['nokendaraan'];
			// $kgwbbruto[$bar['blok']]=$bar['beratbersih'];
			$kgwbpotongan[$bar['nospb']][$bar['blok']]=$bar['kgpotsortasi'];
			// $kgwbnetto[$bar['blok']]=$bar['beratbersih']-$bar['kgpotsortasi'];
			$kgwbbruto[$bar['nospb']][$bar['blok']]=$bar['kgwb'];
			// $kgwbpotongan[$bar['nospb']][$bar['blok']]=0;
			$kgwbnetto[$bar['nospb']][$bar['blok']]=$bar['kgwbnetto'];
			@$nomax++;
			@$tkgwbbruto+=$bar['kgwb'];
			// @$tkgwbpotongan+=0;
			@$tkgwbnetto+=$bar['kgwbnetto'];
			// @$tkgwbbruto+=$bar['beratbersih'];
			@$tkgwbpotongan+=$bar['kgpotsortasi'];
			// @$tkgwbnetto+=$bar['beratbersih']-$bar['kgpotsortasi'];
		}
		// echo"<pre>";
		// print_r($tahuntanampk);

		# Ambil SPB
		$str = "select * from ".$dbname.".pabrik_timbangan_vw  where 
		 		5=5 and kodebarang='40000003' and nospb IN ('".implode("','",$arrnospbex)."')";
		$res = fetchData($str);

		foreach($res as $val) {
			$notiketex[$val['nospb']] = $val['notiket'];
		}
			
		if($nomax=='0'){
			exit("Warning:Belum ada data Surat Pengantar Buah untuk Unit $unit ini");
		}
		$counterr=$nouruttiket=0;

		foreach(@$arrblok as $nospbkey => $val){ 
			foreach($val as $kdblk => $valx){ 
				@$nouruttiket++;
				if($nouruttiket%2==0){
					$bgcolor="style=background-color:lightblue;";
				}else{
					// $bgcolor="bgcolor=beige";
					$bgcolor="";
				}
				@$no++;
				
				#= ambil data grade
				// if($bjrpk[$kdblk]=='' || $bjrpk[$kdblk]==0){
				// 	$tahuntanampk[$kdblk]='A';
				// }else{
				// 	$str="select * from ".$dbname.".pmn_5gradetbs where 
				// 	kodeunit='".$unit."' and batasbawah<='".$bjrpk[$kdblk]."' and batasatas>='".$bjrpk[$kdblk]."' ";
				// 	$res=fetchdata($str);
				// 	$tahuntanampk[$kdblk]=$res[0]['kode'];
				// } 
				
				$tab.="<tr  ".$bgcolor." class=rowcontent id=row".$no.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td id=tanggalpks".$no." hidden>".$tglpk[$nospbkey][$kdblk]."</td>";
					$tab.="<td>".tanggalnormal($tglpk[$nospbkey][$kdblk])."</td>";
					$tab.="<td id=notiket".$no.">".$notiketex[$nospbkey]."</td>";
					$tab.="<td id=kdblok".$no.">".$kdblk."</td>";
					$tab.="<td align=center id=tahuntanam".$no.">".$tahuntanampk[$nospbkey][$kdblk]."</td>";
					$tab.="<td id=nospb".$no." >".$nospb_tiket[$nospbkey][$kdblk]."</td>";
					$tab.="<td>".$nokendaraan[$nospbkey][$kdblk]."</td>";
					$tab.="<td align=right id=bjr".$no.">".number_format($bjrpk[$nospbkey][$kdblk])."</td>";
					$tab.="<td align=right id=kgbruto".$no.">".number_format($kgwbbruto[$nospbkey][$kdblk])."</td>";
					$tab.="<td align=right id=kgpotongan".$no.">".number_format($kgwbpotongan[$nospbkey][$kdblk])."</td>";
					$tab.="<td  align=right id=kgnetto".$no." onblur=hitungtotalrp(".$no.",".$nomax.")>".number_format($kgwbnetto[$nospbkey][$kdblk])."</td>";
						if($datatransaksi==0){
							
							// $str="select * from ".$dbname.".pmn_hargabelitbs where  kodeorg='".$unit."'  and tahuntanam='".$tahuntanampk[$nospbkey][$kdblk]."'  and posting=1 order by tanggal desc limit 1";
							$str="select * from ".$dbname.".pmn_hargabelitbs where  kodeorg='".$unit."'  and tahuntanam='".$tahuntanampk[$nospbkey][$kdblk]."' and tanggal<='".$tglpk[$nospbkey][$kdblk]."' and tanggal2>='".$tglpk[$nospbkey][$kdblk]."'  and posting=1 order by tanggal desc limit 1"; 
							$res=fetchdata($str);

								$datarpkg[$nospbkey][$kdblk]=$res[0]['harga'];
								$datatotalrp[$nospbkey][$kdblk]=$datarpkg[$nospbkey][$kdblk]*$kgwbnetto[$nospbkey][$kdblk];
								
							$tab.="<td align=right><input type=text id=rpkg".$no."  value='".number_format($datarpkg[$nospbkey][$kdblk])."' disabled onblur=hitungtotalrp(".$no.",".$nomax.") onkeyup=\"z.numberFormat('rpkg".$no."');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\" ></td>";
							@$ttotalrp+=$datatotalrp[$nospbkey][$kdblk];
						}else{
							// echo asdasda;
							$tab.="<td align=right><input type=text  id=rpkg".$no."  value='".number_format($datarpkg[$nospbkey][$kdblk])."' disabled onblur=hitungtotalrp(".$no.",".$nomax.") onkeyup=\"z.numberFormat('rpkg".$no."');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>";						
						}
					$tab.="<td align=right><input type=text onblur=hitunggrandtotal(".$nomax.") disabled value='".number_format($datatotalrp[$nospbkey][$kdblk])."' id=totalrp".$no."  onkeyup=\"z.numberFormat('totalrp".$no."');\" class=myinputtextnumber onkeypress=\"return angka_doang(event);\"></td>";										
					if($datarpkg[$nospbkey][$kdblk]==0){
						$tab.="<td><font color=red>Harga Belum diinput/disetujui</font></td>";	
						$counterr++;
					}else{
						$tab.="<td></td>";		
					}	
					
				$tab.="</tr>";
			}
		}
		
		$rp_pph=($ttotalrp*$persenpph)/100;
		$rp_ppn=($ttotalrp*$persenppn)/100;
		$rpgrandtotal=$ttotalrp+$rp_ppn-$rp_pph;
		
		$tab.="<tr class=rowheader bgcolor=#B0C4DE>";
		$tab.="<td align=center colspan=8>".$_SESSION['lang']['total']."</td>";
		$tab.="<td align=right>".@number_format($tkgwbbruto)."</td>";
		$tab.="<td align=right>".@number_format($tkgwbpotongan)."</td>";
		$tab.="<td align=right>".@number_format($tkgwbnetto)."</td>";
		$tab.="<td></td>";
		$tab.="<td id=ttotalrp align=right>".@number_format($ttotalrp)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		$tab.="<tr class=rowheader bgcolor=#B0C4DE>";
		$tab.="<td align=center colspan=8>Ppn</td>";
		$tab.="<td colspan=4></td>";
		$tab.="<td id=rp_ppn align=right>".@number_format($rp_ppn)."</td>";
		$tab.="<td></td>";
 		$tab.="</tr>";
		
		$tab.="<tr class=rowheader bgcolor=#B0C4DE>";
		$tab.="<td align=center colspan=8>Pph</td>";
		$tab.="<td colspan=4></td>";
		$tab.="<td id=rp_pph align=right>".@number_format($rp_pph)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		$tab.="<tr class=rowheader bgcolor=#B0C4DE>";
		$tab.="<td align=center colspan=8>Grand Total</td>";
		$tab.="<td colspan=4></td>";
		$tab.="<td id=rpgrandtotal align=right>".@number_format($rpgrandtotal)."</td>";
		$tab.="<td></td>";
		$tab.="</tr>";
		
		$tab.="<tr class=rowcontent>";
	
		if($counterr>0){
			// $tab.="<td align=center colspan=16><font color=red><b>Data detail sudah disimpan</b></font> &nbsp;&nbsp;";
			$tab.="<td align=center colspan=16><font color=red><b>Masih ada data belum sempurna, tombol simpan tidak akan muncul</b></font> &nbsp;&nbsp;";
		}else{
			$tab.="<td align=center colspan=16><button  id=save class=mybutton onclick=savedt(".$nomax.")>".$_SESSION['lang']['save']."</button>";
		}
		 
		 // $tab.="<td align=center colspan=12><button  id=save class=mybutton onclick=savedt(".$nomax.")>".$_SESSION['lang']['save']."</button>";
		 
		 $tab.="<button id=batal class=mybutton onclick=cancelht()>".$_SESSION['lang']['cancel']."</button>
		<button id=batal class=mybutton onclick=displaylist()>".$_SESSION['lang']['selesai']."</button>
		</td>";	
		// <button id=batal class=mybutton onclick=canceldt()>".$_SESSION['lang']['cancel']."</button></td>";	
		
		echo $tab;
	break;
	

	
    default:
	break;
}
?>
