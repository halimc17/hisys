<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);
use Dompdf\Dompdf;
$stream='';

$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}

switch ($method) {
	/*
	case 'gettipe':
		$opttipetbs = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "SELECT DISTINCT tipetbs FROM ".$dbname.".pmn_5feetbs WHERE kodeunit = '".$param['kodeunit']."' ORDER BY tipetbs ASC";
		// echo $str;exit("Error:A");
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $opttipetbs .= "<option value='" . $bar['tipetbs'] . "'>" . $bar['tipetbs'] . "</option>";
        }
        echo $opttipetbs;
	break;
	*/
	case'getnamakud':
		$_SESSION['tlistsupplier']=array();
		#= array kodesupplier
		$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
			$kodesupplier[$bar['kodept']]=$bar['supplierid'];
		}
		
		$table        ='kebun_tbskud';
		$tableafiliasi='kebun_tbsafiliasi';
		$param['tanggalmulai'] = tanggalsystemn($param['tanggalmulai']);
		$param['tanggalsampai'] = tanggalsystemn($param['tanggalsampai']);
		$where="";
		$where.=" and pemilik like '".$param['kodeunit']."%'";
		$where.=" and tanggaltbs1 between '".$param['tanggalmulai']."' and '".$param['tanggalsampai']."'";
		
		$optnama="<option value=''>". $_SESSION['lang']['all']."</option>";
		$str = "select distinct supplier from ".$dbname.".".$table." where 1=1 ".$where." order by supplier";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optnama.="<option value='".$bar['supplier']."'>".$nmsupplier[$bar['supplier']]."</option>";
		}
		echo $optnama;
	break;
	case'preview':
		$where='';
	
		if($param['kodeunit']==''){
			exit("Warning:Unit tidak boleh kosong");
		}
	
		if($param['tanggalmulai']==''){
			exit("Warning:tanggalmulai tidak boleh kosong");
		}

		if($param['tanggalsampai']==''){
			exit("Warning:tanggalsampai tidak boleh kosong");
		}

		$whr="";
		if ($param['petani']!='') {
			$whr=" and nama ='".$param['petani']."'";
		}
		switch($param['jenis']){
			case'detail':
				
				$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
				$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
				$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
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
				where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
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

				$table        ='kebun_tbskud';
				$tableafiliasi='kebun_tbsafiliasi';
				$param['tanggalmulai'] = tanggalsystemn($param['tanggalmulai']);
				$param['tanggalsampai'] = tanggalsystemn($param['tanggalsampai']);
				$where="";
				$where.=" and pemilik like '".$param['kodeunit']."%'";
				$where.=" and tanggaltbs1 between '".$param['tanggalmulai']."' and '".$param['tanggalsampai']."'";

				$str = "select * from ".$dbname.".".$table." where 1=1 ".$where." ";
				if($param['supplier']!=''){
					$where.=" and supplier = '".$param['supplier']."'";
				}
				
				$str = "select * from ".$dbname.".".$table." where 1=1 ".$where." order by supplier";
				$res = fetchdata($str);
				foreach($res as $bar){
					$notran[$bar['notransaksi']]=$bar['notransaksi'];
				}
				
				$tab.="<table cellpadding=5 class=sortable cellspacing=1>";
				$tab.="<thead><tr class=rowcontent>";
					$tab.="<th align=center><b>No.</b></th>";
					$tab.="<th align=center>".$_SESSION['lang']['nodok']."</th>";
					$tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>"; 
					$tab.="<th align=center>".$_SESSION['lang']['pabrik']."</th>"; 	
					$tab.="<th align=center>".$_SESSION['lang']['supplier']."</th>"; 	
					$tab.="<th align=center><b>".$_SESSION['lang']['periode']."</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['nospb']."</b></th>";
					$tab.="<th align=center><b>Hamparan</b></th>";
					$tab.="<th align=center><b>Kavling</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['nama']."</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['janjang']."</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['brondolan']."</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['kg']." Netto</b></th>";
					$tab.="<th align=center><b>Rp/Kg</b></th>";
					$tab.="<th align=center><b>".$_SESSION['lang']['total']." Netto</b></th>";
				$tab.="</tr></thead><tbody>";
				
				foreach($notran as $notransaksi){					
					$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' ";
					$res = fetchdata($str);
					foreach($res as $bar){
						$supplier=$bar['supplier'];
						$periodetbs=$bar['supplier'];
						$tanggaltbs1=$bar['tanggaltbs1'];
						$tanggaltbs2=$bar['tanggaltbs2'];
						$tanggal=$bar['tanggal'];
						$unit=$bar['unit'];
						$divisi=$bar['divisi'];
						$pemilik=$bar['pemilik'];
					}
						
					// $cellpadding=1;	
					// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:16px'>";
					// $tab.="<tr>";
						// $tab.="<td align=center><b>List Petani Pembayaran TBS</td>"; 	
					// $tab.="</tr>";	
					// $tab.="</table>";	
					// $tab.="<br>";	
					// $tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
					
					// $tab.="<tr>";
						// $tab.="<td align=left>".$_SESSION['lang']['nodok']."</td>"; 	
						// $tab.="<td align=left>:</td>"; 	
						// $tab.="<td align=left>".$notransaksi." </td>";
						
						// $tab.="<td align=left>".$_SESSION['lang']['tanggal']."</td>"; 	
						// $tab.="<td align=left>:</td>"; 	
						// $tab.="<td align=left>".tanggalnormal($tanggal)." </td>";
					// $tab.="</tr>";		
					// $tab.="<tr>";
						// $tab.="<td align=left>".$_SESSION['lang']['pabrik']."</td>"; 	
						// $tab.="<td align=left>:</td>"; 	
						// $tab.="<td align=left>".$nmorg[$unit]." </td>";

						// $tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>"; 	
						// $tab.="<td align=left>:</td>"; 	
						// $tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
					// $tab.="</tr>";	
					// $tab.="<tr>";		
						// $tab.="<td align=left>".$_SESSION['lang']['periode']."</td>"; 	
						// $tab.="<td align=left>:</td>"; 	
						// $tab.="<td align=left>".tanggalnormal($tanggaltbs1)." s/d ".tanggalnormal($tanggaltbs2)." </td>"; 	
					// $tab.="</tr>";	
					// $tab.="</table>";	
					// $tab.="<br>";	
					
					
					
					$listpetani=$listno=array();
					$str = "select nospb, no_hamp, no_kavl, nama, janjang, brondolan, kgwb, kgwbnetto from ".$dbname.".kebun_spbpetani where nospb in (select nospb from ".$dbname.".kebun_tbskud where notransaksi = '".$notransaksi."') ".$whr." order by nospb,no_hamp,no_kavl,nama";

				/*	$str = "select nospb, no_hamp, no_kavl, nama, janjang, brondolan, kgwb, kgwbnetto from ".$dbname.".kebun_spbpetani where nospb in ('0008536/S1PE01/04/2022','0008538/S1PE01/04/2022','0008536/S1PE01/04/2022','0008538/S1PE01/04/2022') ".$whr." order by nospb,no_hamp,no_kavl";*/
			

					$res = fetchdata($str);
					foreach($res as $bar){
						$kunci=$bar['nospb'].$bar['no_hamp'].$bar['no_kavl'];
						$listpetani[$kunci]['nospb']=$bar['nospb'];
						$listpetani[$kunci]['no_hamp']=$bar['no_hamp'];
						$listpetani[$kunci]['no_kavl']=$bar['no_kavl'];
						$listpetani[$kunci]['t_tnm']=$bar['t_tnm'];
						$listpetani[$kunci]['nama']=$bar['nama'];
						$listpetani[$kunci]['janjang']=$bar['janjang'];
						$listpetani[$kunci]['brondolan']=$bar['brondolan'];
						$listpetani[$kunci]['kgwb']=$bar['kgwb'];
						$listpetani[$kunci]['kgwbnetto']=$bar['kgwbnetto'];
						$listno[$kunci]=$kunci;
					}
					$harga=array();
					$str = "select notransaksi, nospb, notiket, rpkg, tanggaltbs1, tanggaltbs2 from ".$dbname.".kebun_tbskud where notransaksi = '".$notransaksi."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$harga[$bar['nospb']]=$bar['rpkg'];
						$periode1[$bar['nospb']]=$bar['tanggaltbs1'];
						$periode2[$bar['nospb']]=$bar['tanggaltbs2'];
					}
					
					$no=0;
					
					// $tab.="<table cellpadding=5 class=sortable cellspacing=0 border=0.5>";
					// $tab.="<thead><tr class=rowcontent>";
						// $tab.="<th align=center><b>No.</b></th>";
						// $tab.="<th align=center>".$_SESSION['lang']['nodok']."</th>";
						// $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>"; 
						// $tab.="<th align=center>".$_SESSION['lang']['pabrik']."</th>"; 	
						// $tab.="<th align=center>".$_SESSION['lang']['supplier']."</th>"; 	
						// $tab.="<th align=center><b>".$_SESSION['lang']['periode']."</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['nospb']."</b></th>";
						// $tab.="<th align=center><b>Hamparan</b></th>";
						// $tab.="<th align=center><b>Kavling</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['nama']."</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['janjang']."</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['brondolan']."</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['kg']." Netto</b></th>";
						// $tab.="<th align=center><b>Rp/Kg</b></th>";
						// $tab.="<th align=center><b>".$_SESSION['lang']['total']." Netto</b></th>";
					// $tab.="</tr></thead><tbody>";
					
					foreach($listno as $kunci){
						$no+=1;
						$tab.="<tr class=rowcontent>";
							$tab.="<td align=right>".$no."</td>";
							$tab.="<td align=left>".$notransaksi." </td>";
							$tab.="<td align=left>".tanggalnormal($tanggal)." </td>";
							$tab.="<td align=left>".$nmorg[$unit]." </td>";
							$tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
							$tab.="<td align=center>".tanggalnormal($periode1[$listpetani[$kunci]['nospb']])." - ".tanggalnormal($periode2[$listpetani[$kunci]['nospb']])."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['nospb']."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['no_hamp']."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['no_kavl']."</td>";
							$tab.="<td align=left>".$listpetani[$kunci]['nama']."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['janjang'])."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['brondolan'],2)."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['kgwbnetto'],2)."</td>";
							$tab.="<td align=right>".number_format($harga[$listpetani[$kunci]['nospb']],2)."</td>";
							$perpetani=$harga[$listpetani[$kunci]['nospb']]*$listpetani[$kunci]['kgwbnetto'];
							$tab.="<td align=right>".number_format($perpetani)."</td>";
						$tab.="</tr>";
						$listpetani['total']['janjang']+=$listpetani[$kunci]['janjang'];
						$listpetani['total']['brondolan']+=$listpetani[$kunci]['brondolan'];
						$listpetani['total']['kgwbnetto']+=$listpetani[$kunci]['kgwbnetto'];
						$listpetani['total']['perpetani']+=$perpetani;
					}
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center><b>".$_SESSION['lang']['total']."</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['janjang'])."</b></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['brondolan'],2)."</b></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['kgwbnetto'],2)."</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['perpetani'])."</b></td>";
					$tab.="</tr>";

				}
				
				$tab.="</tbody>";
				$tab.="</table>";
					
				$stream=$tab;
				switch($param['tipe']){
					case'html':
						echo $stream;
					break;
					case'excel':
						$nop = "Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai'].".xls";
						$xls = new HtmlExcel();
						$xls->setCss($css);
						$xls->addSheet("data", $stream);
						$xls->headers($nop);
						echo $xls->buildFile();
					break;
					case'pdf':
						$dompdf = new Dompdf();
						$dompdf->load_html($stream);
						$dompdf->setPaper('A4', 'landscape');
						$dompdf->render();
						$dompdf->stream("Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai']."_",array("Attachment"=>0));
					break;
				}				
			break;


			case'detail2':
				
				$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
				$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
				$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
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
				where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
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


				$where="";
				#= ambil daftar kantor RO didalam pt bentukan array
				$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)=4 and tipe='KANWIL' ";
				$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()){
					$kodero[$bar['induk']]=$bar['kodeorganisasi'];
				}
				
				if(count($_SESSION['tlistsupplier']) > 0){
					$optnewsc="";
					$nourut=0;
					foreach($_SESSION['tlistsupplier'] as $key=>$val){
						if($nourut==0){
							$optnewsc .= "'".$val['supplier']."'";
						}else{
							$optnewsc .= ",'".$val['supplier']."'";
						}
						$nourut++;
					}
					$where.=" and supplier in (".$optnewsc.")";
				}
				
				$table        ='kebun_tbskud';
				$tableafiliasi='kebun_tbsafiliasi';
				$param['tanggalmulai'] = tanggalsystemn($param['tanggalmulai']);
				$param['tanggalsampai'] = tanggalsystemn($param['tanggalsampai']);
				$where.=" and pemilik like '".$param['kodeunit']."%'";
				$where.=" and tanggaltbs1 between '".$param['tanggalmulai']."' and '".$param['tanggalsampai']."'";
				
				$str = "select * from ".$dbname.".".$table." where 1=1 ".$where." ";
			/*	$str = "select tanggaltbs1,tanggaltbs2,supplier,a.nospb,no_hamp,no_kavl,nama,janjang,brondolan,kgwb,kgwbnetto,rpkg from ".$dbname.".".$table." a left join ".$dbname.".kebun_spbpetani b on a.nospb=b.nospb where 1=1 ".$where." group by nama,nospb order by nama ";
				*/
				$res = fetchdata($str);
				foreach($res as $bar){
				
					$arrdata[$bar['supplier']]=$bar['supplier'];
					
				}

				

				$str = "select tanggaltbs1,tanggaltbs2,supplier,a.nospb,no_hamp,no_kavl,nama,janjang,brondolan,kgwb,kgwbnetto,rpkg from ".$dbname.".".$table." a left join ".$dbname.".kebun_spbpetani b on a.nospb=b.nospb where 1=1 ".$where."   group by nama,nospb order by nama asc,tanggaltbs1 asc";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						if (substr($bar['tanggaltbs1'],8,2) <= 15) {
							$arrdatax[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['nospb'];
							$arrtotnet[$bar['supplier']][$bar['nama']]['per1']+=$bar['kgwbnetto']*$bar['rpkg'];

							$arrperiode[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['tanggaltbs1'].' - '.$bar['tanggaltbs2'];
							$arrhamp[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['no_hamp'];
							$arrkavl[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['no_kavl'];
							$arrjjg[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['janjang'];
							$arrbrd[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['brondolan'];
							$arrkgnet[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['kgwbnetto'];
							$arrrpkg[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=$bar['rpkg'];


							$arrcols[$bar['supplier']][$bar['nama']]['per1']=1;
							$arrcols2[$bar['supplier']][$bar['nama']]['per1'][$bar['nospb']]=1;
						}
						else
						{
							$arrdatax[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['nospb'];
							$arrtotnet[$bar['supplier']][$bar['nama']]['per2']+=$bar['kgwbnetto']*$bar['rpkg'];


							$arrperiode[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['tanggaltbs1'].' - '.$bar['tanggaltbs2'];
							$arrhamp[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['no_hamp'];
							$arrkavl[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['no_kavl'];
							$arrjjg[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['janjang'];
							$arrbrd[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['brondolan'];
							$arrkgnet[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['kgwbnetto'];
							$arrrpkg[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=$bar['rpkg'];


							$arrcols[$bar['supplier']][$bar['nama']]['per2']=1;
							$arrcols2[$bar['supplier']][$bar['nama']]['per2'][$bar['nospb']]=1;
						}
					
					}
					foreach($arrdatax as $supplier => $v){	
						foreach ($v as $nama => $v2) {
							foreach ($v2 as $per => $v3) {
									$cols[$supplier][$nama]+=$arrcols[$supplier][$nama][$per];
								foreach ($v3 as $spb => $v4) {
									$cols2[$supplier][$nama]+=$arrcols2[$supplier][$nama][$per][$spb];
									$cols3[$supplier][$nama][$per]+=$arrcols2[$supplier][$nama][$per][$spb];
									
							    }

							}

						}

					}

	

				
				$jlhkud=0;
				$gtunitnetto=$gtunitpph=$gtunitbrd=$gtunitarrkgnet=0;
				foreach($arrdatax as $supplier => $v){					
					$jlhkud++;
						
					$cellpadding=1;	
					$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:16px'>";
					$tab.="<tr>";
						$tab.="<td align=center><b>List Petani Pembayaran TBS</td>"; 	
					$tab.="</tr>";	
					$tab.="</table>";	
					$tab.="<br>";	
					$tab.="<table cellpadding=".$cellpadding." cellspacing=0 border=0 style='font-size:12px'>";
					
					$tab.="<tr style='font-weight:bold'>";
						

						$tab.="<td align=left>".$_SESSION['lang']['supplier']."</td>"; 	
						$tab.="<td align=left>:</td>"; 	
						$tab.="<td align=left>".$nmsupplier[$supplier]." </td>";
						
					$tab.="</tr>";	
					
					$tab.="</table>";	
					$tab.="<br>";	

				
					
					$tab.="<table width=100%  cellpadding=5 class=sortable cellspacing=0 border=1>";
					$tab.="<thead><tr class=rowcontent>";
						$tab.="<td align=right><b>No.</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['nama']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['total']." Netto</b></td>";
						$tab.="<td align=center><b>Pph 22</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['nospb']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['periode']."</b></td>";
						$tab.="<td align=center><b>Hamparan</b></td>";
						$tab.="<td align=center><b>Kavling</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['janjang']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['brondolan']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['kg']." Netto</b></td>";
						$tab.="<td align=center><b>Rp/Kg</b></td>";
					$tab.="</tr></thead><tbody>";

					$optnpwp=makeOption($dbname,"log_5supnpwp",'supplierid,npwp',"supplierid='".$supplier."'");

					$no=$gtnetto=$gtpph=$gtbrd=$gtarrkgnet=0;
					foreach ($v as $nama => $v2) {
							

							$col=$cols2[$supplier][$nama];
							
							$no+=1;
							$nox=0;

							
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=left rowspan=".$col.">".$no." </td>";
							$tab.="<td align=left rowspan=".$col.">".$nama."</td>";

							//$optnpwp=makeOption($dbname,"kebun_5kavling",'nama,npwp',"nama='".$nama."'");
							
							
						$totnetto=0;
						$totpph=0;
						$totbrd=0;
						$totarrkgnet=0;
						$totarrrpkg=0;
						foreach ($v2 as $per => $v3) {
							$nox+=1;
							$col2=$cols3[$supplier][$nama][$per];
							
							if ($nox==1) {
								$noxx=0;
								// $jum=$arrtotnet[$supplier][$nama]['per1']+$arrtotnet[$supplier][$nama]['per2'];
								$jum=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));
								if ($jum<20000000) {
										$pph[$supplier][$nama][$per]=0;
								}
								if ($arrtotnet[$supplier][$nama]['per1']<20000000) {
										$pph[$supplier][$nama]['per1']=0;
									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
								
								if ($arrtotnet[$supplier][$nama]['per1']>20000000) {
									if ($optnpwp[$supplier]==0) {
										$pph[$supplier][$nama]['per1']=$jum*(0.5/100);
									}
									else
									{
										$pph[$supplier][$nama]['per1']=$jum*(0.25/100);
									}

									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}

								$color='';
								if ($pph[$supplier][$nama][$per]>0) {
									$color="style='background-color:cyan';";
								}

								$nilpph=explode(".",$pph[$supplier][$nama][$per]);
								$pph[$supplier][$nama][$per]=$nilpph[0];
								$tab.="<td align=right rowspan=".$col2.">".number_format($arrtotnet[$supplier][$nama][$per],0)."</td>";
								$tab.="<td align=right rowspan=".$col2." ".$color.">".number_format($pph[$supplier][$nama][$per],0)."</td>";
								$arrAdaPPh[$supplier][$nama]=0;
								if($pph[$supplier][$nama][$per]>0){
									$arrAdaPPh[$supplier][$nama]=1;
								}
								foreach ($v3 as $spb => $v4) {
									$noxx+=1;
									
									if ($noxx==1) {
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}
									else
									{
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}

									$totarrkgnet+=$arrkgnet[$supplier][$nama][$per][$spb];
									$totbrd+=$arrbrd[$supplier][$nama][$per][$spb];
									$totarrrpkg+=$arrrpkg[$supplier][$nama][$per][$spb];
								}
								

								
							}
							else
							{
								$col2=$cols3[$supplier][$nama][$per];

								$noxx=0;
								// $jum=$arrtotnet[$supplier][$nama]['per1']+$arrtotnet[$supplier][$nama]['per2'];
								// $jum=$arrtotnet[$supplier][$nama][$per];
								$jum=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));

								if ($jum<20000000) {
										$pph[$supplier][$nama][$per]=0;
								}
								if ($arrtotnet[$supplier][$nama]['per1']<20000000) {
										$pph[$supplier][$nama]['per1']=0;
									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
								if ($arrtotnet[$supplier][$nama]['per1']>20000000) {
									if ($optnpwp[$supplier]==0) {
										$pph[$supplier][$nama]['per1']=$jum*(0.5/100);
									}
									else
									{
										$pph[$supplier][$nama]['per1']=$jum*(0.25/100);
									}

									if ($jum>=20000000) {
										if ($optnpwp[$supplier]==0) {
											$pph[$supplier][$nama]['per2']=$jum*(0.5/100);
										}
										else
										{
											$pph[$supplier][$nama]['per2']=$jum*(0.25/100);
										}
										
									}
								}
									$nilpph=explode(".",$pph[$supplier][$nama][$per]);
									$pph[$supplier][$nama][$per]=$nilpph[0];
								$color='';
								if ($pph[$supplier][$nama][$per]>0) {
									$color="style='background-color:cyan';";
								}
								$tab.="<tr class=rowcontent>";
							    $tab.="<td align=right rowspan=".$col2.">".number_format($arrtotnet[$supplier][$nama][$per],0)."</td>";
							    $tab.="<td align=right rowspan=".$col2." ".$color.">".number_format($pph[$supplier][$nama][$per],0)."</td>";
								$arrAdaPPh2[$supplier][$nama]=0;
								if($pph[$supplier][$nama][$per]>0){
									$arrAdaPPh2[$supplier][$nama]=1;
								}
							    $noxx=0;
							    foreach ($v3 as $spb => $v4) {
									$noxx+=1;
									
									if ($noxx==1) {
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}
									else
									{
										$tab.="<tr class=rowcontent>";
										$tab.="<td align=left >".$spb."</td>";
										$tab.="<td align=left >".$arrperiode[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrhamp[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=left >".$arrkavl[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".$arrjjg[$supplier][$nama][$per][$spb]."</td>";
										$tab.="<td align=right >".number_format($arrbrd[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrkgnet[$supplier][$nama][$per][$spb],0)."</td>";
										$tab.="<td align=right >".number_format($arrrpkg[$supplier][$nama][$per][$spb],0)."</td>";
									}

									$totarrkgnet+=$arrkgnet[$supplier][$nama][$per][$spb];
									$totbrd+=$arrbrd[$supplier][$nama][$per][$spb];
									$totarrrpkg+=$arrrpkg[$supplier][$nama][$per][$spb];
								}
							   
							    $tab.="</tr>";
					
							}

							$totnetto+=floatval(str_replace(",","",(number_format($arrtotnet[$supplier][$nama][$per]))));
							$totpph+=$pph[$supplier][$nama][$per];
							
					

							
						}
						$totpph=0;
						if($totnetto>=20000000) {
							if ($optnpwp[$supplier]==0) {
								$totpph=$totnetto*(0.5/100);
							}
							else
							{
								$totpph=$totnetto*(0.25/100);
							}
						}
						if($totpph>0){
							$nilpph=explode(".",$totpph);
							$totpph=$nilpph[0];
						}
						
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center colspan=2><b>Total</b></td>";
						$tab.="<td align=right><b>".number_format($totnetto,0)."</b></td>";
						$tab.="<td align=right><b>".number_format($totpph,0)."</b></td>";
						$tab.="<td align=right colspan=5><b></b></td>";
						$tab.="<td align=right><b>".number_format($totbrd,0)."</b></td>";
						$tab.="<td align=right><b>".number_format($totarrkgnet,0)."</b></td>";
						// $tab.="<td align=right><b>".number_format($totarrrpkg,0)."</b></td>";
						$tab.="<td align=right><b></b></td>";
						$tab.="</tr>";
						
						$gtnetto+=$totnetto;
						$gtbrd+=$totbrd;
						$gtarrkgnet+=$totarrkgnet;
					}
					
					if ($optnpwp[$supplier]==0) {
						$gtpph=floor($gtnetto*(0.5/100));
					}
					else
					{
						$gtpph=floor($gtnetto*(0.25/100));
					}

					$tab.="<tr class=rowcontent style='background-color:#69fc57'>";
					$tab.="<td align=center colspan=2><b>Grand Total ".$nmsupplier[$supplier]."</b></td>";
					$tab.="<td align=right><b>".number_format($gtnetto,0)."</b></td>";
					$tab.="<td align=right><b>".number_format($gtpph,0)."</b></td>";
					$tab.="<td align=right colspan=5><b></b></td>";
					$tab.="<td align=right><b>".number_format($gtbrd,0)."</b></td>";
					$tab.="<td align=right><b>".number_format($gtarrkgnet,0)."</b></td>";
					$tab.="<td align=right><b></b></td>";
					$tab.="</tr>";
					
					$gtunitnetto+=$gtnetto;
					$gtunitbrd+=$gtbrd;
					$gtunitarrkgnet+=$gtarrkgnet;
					
					if($jlhkud==count($arrdatax)){
						$tab.="<tr>";
						$tab.="<td colspan=12>&nbsp</td>";
						$tab.="</tr>";
						
						if ($optnpwp[$supplier]==0) {
							$gtunitpph=floor($gtunitnetto*(0.5/100));
						}
						else
						{
							$gtunitpph=floor($gtunitnetto*(0.25/100));
						}
						
						$tab.="<tr class=rowcontent style='background-color:#ff5a5a'>";
						$tab.="<td align=center colspan=2><b>Grand Total</b></td>";
						$tab.="<td align=right><b>".number_format($gtunitnetto,0)."</b></td>";
						$tab.="<td align=right><b>".number_format($gtunitpph,0)."</b></td>";
						$tab.="<td align=right colspan=5><b></b></td>";
						$tab.="<td align=right><b>".number_format($gtunitbrd,0)."</b></td>";
						$tab.="<td align=right><b>".number_format($gtunitarrkgnet,0)."</b></td>";
						$tab.="<td align=right><b></b></td>";
						$tab.="</tr>";
					}
					


					
					
					/*$listpetani=$listno=array();
					$str = "select nospb, no_hamp, no_kavl, nama, janjang, brondolan, kgwb, kgwbnetto from ".$dbname.".kebun_spbpetani where nospb in (select nospb from ".$dbname.".kebun_tbskud where notransaksi = '".$notransaksi."') ".$whr." order by nospb,no_hamp,no_kavl,nama";			

					$res = fetchdata($str);
					foreach($res as $bar){
						$kunci=$bar['nospb'].$bar['no_hamp'].$bar['no_kavl'];
						$listpetani[$kunci]['nospb']=$bar['nospb'];
						$listpetani[$kunci]['no_hamp']=$bar['no_hamp'];
						$listpetani[$kunci]['no_kavl']=$bar['no_kavl'];
						$listpetani[$kunci]['t_tnm']=$bar['t_tnm'];
						$listpetani[$kunci]['nama']=$bar['nama'];
						$listpetani[$kunci]['janjang']=$bar['janjang'];
						$listpetani[$kunci]['brondolan']=$bar['brondolan'];
						$listpetani[$kunci]['kgwb']=$bar['kgwb'];
						$listpetani[$kunci]['kgwbnetto']=$bar['kgwbnetto'];
						$listno[$kunci]=$kunci;
					}
					$harga=array();
					$str = "select notransaksi, nospb, notiket, rpkg, tanggaltbs1, tanggaltbs2 from ".$dbname.".kebun_tbskud where notransaksi = '".$notransaksi."'";
					$res = fetchdata($str);
					foreach($res as $bar){
						$harga[$bar['nospb']]=$bar['rpkg'];
						$periode1[$bar['nospb']]=$bar['tanggaltbs1'];
						$periode2[$bar['nospb']]=$bar['tanggaltbs2'];
					}
					
					$no=0;
					
					$tab.="<table width=100%  cellpadding=5 class=sortable cellspacing=0 border=0.5>";
					$tab.="<thead><tr class=rowcontent>";
						$tab.="<td align=right><b>No.</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['periode']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['nospb']."</b></td>";
						$tab.="<td align=center><b>Hamparan</b></td>";
						$tab.="<td align=center><b>Kavling</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['nama']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['janjang']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['brondolan']."</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['kg']." Netto</b></td>";
						$tab.="<td align=center><b>Rp/Kg</b></td>";
						$tab.="<td align=center><b>".$_SESSION['lang']['total']." Netto</b></td>";
					$tab.="</tr></thead><tbody>";
					
					foreach($listno as $kunci){
						$no+=1;
						$tab.="<tr class=rowcontent>";
							$tab.="<td align=right>".$no."</td>";
							$tab.="<td align=center>".$periode1[$listpetani[$kunci]['nospb']]." - ".$periode2[$listpetani[$kunci]['nospb']]."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['nospb']."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['no_hamp']."</td>";
							$tab.="<td align=center>".$listpetani[$kunci]['no_kavl']."</td>";
							$tab.="<td align=left>".$listpetani[$kunci]['nama']."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['janjang'])."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['brondolan'],2)."</td>";
							$tab.="<td align=right>".number_format($listpetani[$kunci]['kgwbnetto'],2)."</td>";
							$tab.="<td align=right>".number_format($harga[$listpetani[$kunci]['nospb']],2)."</td>";
							$perpetani=$harga[$listpetani[$kunci]['nospb']]*$listpetani[$kunci]['kgwbnetto'];
							$tab.="<td align=right>".number_format($perpetani)."</td>";
						$tab.="</tr>";
						$listpetani['total']['janjang']+=$listpetani[$kunci]['janjang'];
						$listpetani['total']['brondolan']+=$listpetani[$kunci]['brondolan'];
						$listpetani['total']['kgwbnetto']+=$listpetani[$kunci]['kgwbnetto'];
						$listpetani['total']['perpetani']+=$perpetani;
					}
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center><b>".$_SESSION['lang']['total']."</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['janjang'])."</b></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['brondolan'],2)."</b></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['kgwbnetto'],2)."</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($listpetani['total']['perpetani'])."</b></td>";
					$tab.="</tr>";

					//$supplier

					#= ambil status NPWP Supp
					$str = "select npwp from ".$dbname.".log_5supnpwp where  supplierid='".$supplier."' and active=1";
					$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()){
						$npwp=$bar['npwp'];
					}

					if ($npwp=='') {
						$persen=0.5;
					}
					else
					{
						$persen=0.25;
					}
					$pph22=0;
					if ($listpetani['total']['perpetani']>= 20000000) {
						$pph22=($listpetani['total']['perpetani']*($persen/100));
					}
					

					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center><b>".$_SESSION['lang']['total']." Pph22</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($pph22)."</b></td>";
					$tab.="</tr>";

					$selisih=$listpetani['total']['perpetani']-$pph22;
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center><b>Grand ".$_SESSION['lang']['total']."</b></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=right></td>";
					$tab.="<td align=center></td>";
					$tab.="<td align=right><b>".number_format($selisih)."</b></td>";
					$tab.="</tr>";*/

					$tab.="</tbody>";
					$tab.="</table><br>";
				}
				
				$stream=$tab;
				switch($param['tipe']){
					case'html':
						echo $stream;
					break;
					case'excel':
						$nop = "Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai'].".xls";
						$xls = new HtmlExcel();
						$xls->setCss($css);
						$xls->addSheet("data", $stream);
						$xls->headers($nop);
						echo $xls->buildFile();
					break;
					case'pdf':
						$dompdf = new Dompdf();
						$dompdf->load_html($stream);
						$dompdf->setPaper('A4', 'landscape');
						$dompdf->render();
						$dompdf->stream("Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai']."_",array("Attachment"=>0));
					break;
				}				
			break;
			default:
			
				if ($param['tipetbs'] == 'SUPPLIERTBSAFI') {
					$table = 'kebun_tbsafiliasi';
					$where=" and pemilik='".$param['kodeunit']."'";
					$judullaporan='REKAPITULASI PEMBAYARAN TBS AFILIASI';
				} else if ($param['tipetbs'] == 'SUPPLIERTBSEXT') {
					$table = 'kebun_tbsexternal';
					$where=" and unit='".$param['kodeunit']."'";
					$judullaporan='REKAPITULASI PEMBAYARAN TBS EXTERNAL SWADAYA';
				} else if ($param['tipetbs'] == 'SUPPLIERTBSKUD') {
					$table = 'kebun_tbskud';
					$where=" and pemilik='".$param['kodeunit']."'";
					$judullaporan='REKAPITULASI PEMBAYARAN TBS PETANI KUD';
				} else if ($param['tipetbs'] == 'SUPPLIERTBSINT') {
					$table = 'kebun_tbsinternal';
					$where=" and divisi='".$param['kodeunit']."'";
					$judullaporan='DAFTAR TAGIHAN PEMBAYARAN TBS INTERNAL<BR>'.$namaorganisasi[$param['kodeunit']].'';
				} 
				
				#= jika external pakai unit= ; jika afiliasi dan kud pakai pemilik
				
				
				// #= tbs
				// $str="select sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,supplier from ".$dbname.".".$table." where 1=1 ".$where." and tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' group by supplier";
				// $res=fetchdata($str);
				// foreach($res as $bar){
				// 	$arrkodesupplier[$bar['supplier']]=$bar['supplier'];
				// 	$dtkgnetto[$bar['supplier']]=$bar['kgnetto'];
				// 	$dtrptbs[$bar['supplier']]=$bar['totalrp'];
				// } 
				
				// #= fee
				// $str="select sum(totalrp) as totalrp,kodesupplier from ".$dbname.".pmn_feetbs where kodesupplier in ('".implode("','",$arrkodesupplier)."') and  tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' group by kodesupplier";
				// $res=fetchdata($str);
				// foreach($res as $bar){
				// 	$dtrpfee[$bar['kodesupplier']]=$bar['totalrp'];
				// } 
				
				// #= nama supplier
				// $str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrkodesupplier)."')";
				// $res=fetchdata($str);
				// foreach($res as $bar){
				// 	$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
				// }

				#= tbs
				$str="select kgnetto,totalrp,supplier,tanggaltbs1,tanggaltbs2, nospb from ".$dbname.".".$table." where 1=1 ".$where." and tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' order by tanggaltbs1";
				$res=fetchdata($str);
				foreach($res as $bar){
					$arrkodesupplier[$bar['supplier']] = $bar['supplier'];
					$arrrangetanggal[$bar['tanggaltbs1']."-".$bar['tanggaltbs2']] = $bar['tanggaltbs1']."-".$bar['tanggaltbs2'] ;
					$dtkgnetto[$bar['tanggaltbs1']."-".$bar['tanggaltbs2']][$bar['supplier']] = $bar['kgnetto'];
					$dtrptbs[$bar['tanggaltbs1']."-".$bar['tanggaltbs2']][$bar['supplier']] = $bar['totalrp'];
					$arrsupplierbytanggal[$bar['tanggaltbs1']."-".$bar['tanggaltbs2']][$bar['supplier']] = $bar['supplier'];

					$dtkgnettosums[] = $bar['kgnetto'];
					$dtrptbssums[] = $bar['totalrp'];
				} 
				#= fee
				$str="select totalrp,kodesupplier, tanggaltbs1, tanggaltbs2 from ".$dbname.".pmn_feetbs where kodesupplier in ('".implode("','",$arrkodesupplier)."') and  tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' order by tanggaltbs1";
				$res=fetchdata($str);
				foreach($res as $bar){
					$dtrpfee[$bar['tanggaltbs1']."-".$bar['tanggaltbs2']][$bar['kodesupplier']]=$bar['totalrp'];

					$dtrpfeesums[] = $bar['totalrp'];
				} 
				
				#= nama supplier
				$str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrkodesupplier)."')";
				$res=fetchdata($str);
				foreach($res as $bar){
					$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
				}
				
				if($param['tipe']=='html'){
					$stylekolom='border=0 cellspacing=1';
				}else if($param['tipe']=='pdf'){
					$stylekolom='border=1 cellspacing=0';
					
					$stream.="<table class=sortable  width=100% border=0>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
					$stream.="</tr>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6><b>PERIODE ".tanggalnormal($param['tanggalmulai'])." s/d ".tanggalnormal($param['tanggalsampai'])."<b></th>";
					$stream.="</tr>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6>&nbsp;</th>";
					$stream.="</tr>";
					$stream.="</table>";
					
				}else if($param['tipe']=='excel'){
					$stylekolom='border=1 cellspacing=1';
					
					$stream.="<table class=sortable  width=100% border=0>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
					$stream.="</tr>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6><b>PERIODE ".$param['tanggalmulai']." s/d ".$parram['tanggalsampai']."<b></th>";
					$stream.="</tr>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center colspan=6>&nbsp;</th>";
					$stream.="</tr>";
					$stream.="</table>";
				}
				/*
				$stream.="<th align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['nodok']."</th>";
				*/
				// $border='border=0';
				$stream.="<table class=sortable ".$stylekolom." width=100%>";
				$stream.="<thead>";
					$stream.="<tr class=rowheader>";		
						$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
						$stream.="<th align=center>".$_SESSION['lang']['supplier']."</th>";
						$stream.="<th align=center>".$_SESSION['lang']['netto']." (Kg)</th>";
						$stream.="<th align=center>".$_SESSION['lang']['biayaadministrasi']." (Rp)</th>";
						$stream.="<th align=center>".$_SESSION['lang']['total']." (Rp)</th>";
						$stream.="<th align=center>".$_SESSION['lang']['subtotal']." (Rp)</th>";
						
					$stream.="</tr>";
					$stream.="</thead>";
					$stream.="<tbody>";
					$no=0;
					// foreach($arrkodesupplier as $dtkodesupplier){
					// 	$no++;
					// 	$stream.="<tr class=rowcontent>";		
					// 		$stream.="<td align=center>".$no."</td>";
					// 		$stream.="<td>".$namasupplier[$dtkodesupplier]."</td>";
							// $dtsubtotal[$dtkodesupplier]=$dtrpfee[$dtkodesupplier]+$dtrptbs[$dtkodesupplier];
							// @$stdtkgnetto+=$dtkgnetto[$dtkodesupplier];
							// @$stdtrpfee+=$dtrpfee[$dtkodesupplier];
							// @$stdtrptbs+=$dtrptbs[$dtkodesupplier];
					// 		$stream.="<td align=right>".hidezerodecimal($dtkgnetto[$dtkodesupplier])."</td>";
					// 		$stream.="<td align=right>".hidezerodecimal($dtrpfee[$dtkodesupplier])."</td>";
					// 		$stream.="<td align=right>".hidezerodecimal($dtrptbs[$dtkodesupplier])."</td>";
					// 		$stream.="<td align=right>".hidezerodecimal($dtsubtotal[$dtkodesupplier])."</td>";
					// 	$stream.="</tr>";
					// }
					foreach ($arrrangetanggal as $rangetanggal) {
						foreach ($arrsupplierbytanggal[$rangetanggal] as $supplierbytanggal) {
							$no++;
							$stream.="<tr class=rowcontent>";
								$stream.="<td align=center>".$no."</td>";
								$stream.="<td>".$namasupplier[$supplierbytanggal]."</td>";
								$dtsubtotal[$rangetanggal][$supplierbytanggal]=$dtrpfee[$rangetanggal][$supplierbytanggal]+$dtrptbs[$rangetanggal][$supplierbytanggal];
								@$stdtkgnetto=array_sum($dtkgnettosums);
								@$stdtrpfee=array_sum($dtrpfeesums);
								@$stdtrptbs=array_sum($dtrptbssums);
								$stream.="<td align=right>".hidezerodecimal($dtkgnetto[$rangetanggal][$supplierbytanggal])."</td>";
								$stream.="<td align=right>".hidezerodecimal($dtrpfee[$rangetanggal][$supplierbytanggal])."</td>";
								$stream.="<td align=right>".hidezerodecimal($dtrptbs[$rangetanggal][$supplierbytanggal])."</td>";
								$stream.="<td align=right>".hidezerodecimal($dtsubtotal[$rangetanggal][$supplierbytanggal])."</td>";
							$stream.="</tr>";
							// echo "<pre>"; print_r($stdtkgnetto); exit;
						}
						$stream.="<tr style=background-color:yellow;>";
							$stream.="<td colspan=2 align=center>PERIODE ".$rangetanggal."</td>";
							$stream.="<td align=right>".hidezerodecimal(array_sum($dtkgnetto[$rangetanggal]))."</td>";
							$stream.="<td align=right>".hidezerodecimal(array_sum($dtrpfee[$rangetanggal]))."</td>";
							$stream.="<td align=right>".hidezerodecimal(array_sum($dtrptbs[$rangetanggal]))."</td>";
							$stream.="<td align=right>".hidezerodecimal(array_sum($dtsubtotal[$rangetanggal]))."</td>";
						$stream.="</tr>";
					}
					$stream.="<tr class=rowcontent>";		
						$stream.="<td></td>";
						$stream.="<td></td>";
						$stream.="<td></td>";
						$stream.="<td></td>";
						$stream.="<td></td>";
						$stream.="<td></td>";
					$stream.="</tr>";
					$stream.="<tr class=rowcontent>";		
						$stream.="<td></td>";
						$stream.="<td></td>";
						$stdtsubtotal=$stdtrpfee+$stdtrptbs;
						$stream.="<td align=right><b>".hidezerodecimal($stdtkgnetto)."</b></td>";
						$stream.="<td align=right><b>".hidezerodecimal($stdtrpfee)."</b></td>";
						$stream.="<td align=right><b>".hidezerodecimal($stdtrptbs)."</b></td>";
						$stream.="<td align=right><b>".hidezerodecimal($stdtsubtotal)."</b></td>";
					$stream.="</tr>";
				$stream.="</tbody>";
				$stream.="</table>";

				$stream.=getketeranganttd('pmn_2rekappembeliandanabmintbs',$param['tipe'],$param['kodeunit']);	
				switch($param['tipe']){
					case'html':
						echo $stream;
					break;
					case'excel':
						$nop = "Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai'].".xls";
						$xls = new HtmlExcel();
						$xls->setCss($css);
						$xls->addSheet("data", $stream);
						$xls->headers($nop);
						echo $xls->buildFile();
					break;
					// break;
					case'pdf':
					$dompdf = new Dompdf();
					$dompdf->load_html($stream);
					$dompdf->setPaper('A4', 'landscape');
					$dompdf->render();
					$dompdf->stream("Rekap_PembelianTBS_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai']."_",array("Attachment"=>0));
					break;
				}
			break;
		}
		
		
		

		
	break;
	
	case'chooseTarget':
		if($param['supplier']!=''){
			$newdata = array('supplier'=>$param['supplier'],'deskripsi'=>$param['nsupplier']);
			array_push($_SESSION['tlistsupplier'],$newdata);
		}
	break;
	
	case'loadlistsupplier':
		$tab="";
		foreach($_SESSION['tlistsupplier'] as $key=>$val){
			$tab.="<div class='choosed noselect' onclick=\"deletelistsupplier('".$val['supplier']."')\">".$val['deskripsi']."</div>";
		}
		
		echo $tab;
	break;
	
	case'loadoptsupplier':
		$nourut=0;
		$optnewsc="";
		foreach($_SESSION['tlistsupplier'] as $key=>$val){
			if($nourut==0){
				$optnewsc .= "'".$val['supplier']."'";
			}else{
				$optnewsc .= ",'".$val['supplier']."'";
			}
			$nourut++;
		}
		
		$arrnewsc="";
		
		#= array kodesupplier
		$str = "SELECT a.supplierid,a.namasupplier,a.kodept FROM " . $dbname . ".log_5supplier a
		left join log_5supkelompok b on a.supplierid=b.supplierid
		where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()){
			$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
			$kodesupplier[$bar['kodept']]=$bar['supplierid'];
		}
		
		$table        ='kebun_tbskud';
		$tableafiliasi='kebun_tbsafiliasi';
		$param['tanggalmulai'] = tanggalsystemn($param['tanggalmulai']);
		$param['tanggalsampai'] = tanggalsystemn($param['tanggalsampai']);
		$where="";
		$where.=" and pemilik like '".$param['kodeunit']."%'";
		$where.=" and tanggaltbs1 between '".$param['tanggalmulai']."' and '".$param['tanggalsampai']."'";
		if($optnewsc!=""){
			$where.=" and supplier not in (".$optnewsc.")";
		}
		
		$optnama="<option value=''>". $_SESSION['lang']['all']."</option>";
		$str = "select distinct supplier from ".$dbname.".".$table." where 1=1 ".$where." order by supplier";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optnama.="<option value='".$bar['supplier']."'>".$nmsupplier[$bar['supplier']]."</option>";
		}
		
		echo $optnama;
	break;
	
	case'deletelistsupplier':
		foreach($_SESSION['tlistsupplier'] as $key=>$val){
			if($val['supplier'] == $param['supplier']){
				unset($_SESSION['tlistsupplier'][$key]);
			}
		}
	break;
}



?>