<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('phpqrcode/qrlib.php');
error_reporting(0);
$kodeorg        =checkPostGet('kodeorg','');
$jenisbiaya     =checkPostGet('jenisbiaya','');
$tipe           =checkPostGet('tipe','');
$kodeasset      =checkPostGet('kodeasset','');
$kodeasetlama   =checkPostGet('kodeasetlama','');
$kodebarang     =checkPostGet('kodebarang','');
$namaaset       =checkPostGet('namaaset','');
$tanggalperolehan =tanggalsystemn(checkPostGet('tahunperolehan',''));
$nilaiperolehan =checkPostGet('nilaiperolehan','');
$jumlahbulan    =checkPostGet('jumlahbulan','');
$bulanawal      =checkPostGet('bulanawal','');
$keterangan     =checkPostGet('keterangan','');
$status         =checkPostGet('status','');
$method         =checkPostGet('method','');
$leasing        =checkPostGet('leasing','');
$penambah       =checkPostGet('penambah','');
$pengurang      =checkPostGet('pengurang','');
$refbayar       =checkPostGet('refbayar','');
$nodokpengadaan =checkPostGet('nodokpengadaan','');
$persendecline  =checkPostGet('persendecline','');
$posisiasset    =checkPostGet('posisiasset','');
$induk          =checkPostGet('induk','');
$sub            =checkPostGet('sub','');
$namafile 		= checkPostGet('namafile', '');
$tipelokasiasset= checkPostGet('tipelokasiasset', '');

$kodeorgsch 		= checkPostGet('kodeorgsch', '');
$kodeasetlamasch  	= checkPostGet('kodeasetlamasch', '');
$tipesch 			= checkPostGet('tipesch', '');
$tipelokasiassetsch = checkPostGet('tipelokasiassetsch', '');
$subsch 			= checkPostGet('subsch', '');
$bulanawalsch 		= checkPostGet('bulanawalsch', '');
$posisiassetsch 	= checkPostGet('posisiassetsch', '');
$statussch  		= checkPostGet('statussch', '');
$namaasetsch  		= checkPostGet('namaasetsch', '');
$kodeasetsch  		= checkPostGet('kodeasetsch', '');
$kodeprojectsch  		= checkPostGet('kodeprojectsch', '');

$nomesin= checkPostGet('nomesin', '');
$norangka= checkPostGet('norangka', '');
$tipemodel= checkPostGet('tipemodel', '');
$penyusutantambahan= checkPostGet('penyusutantambahan', '');
$tanggaldisposal=tanggalsystemn(checkPostGet('tanggaldisposal',''));
$optTpasset     =makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,metodepenyusutan');
$kamusleasing[0]='Not Leasing';
$kamusleasing[1]='Leasing';
$folder="fileupload/qrcodeasset/";
if($penambah==''){
$penambah=0;
}
if($pengurang==''){
$pengurang=0;
}
if($jumlahbulan!=='' and $jumlahbulan!='' and $jumlahbulan>0)
   $bulanan=$nilaiperolehan/$jumlahbulan;
else
  $bulanan=0; 
 

$dmn="char_length(kodeorganisasi)='4'";
$orgOption=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', $dmn,'2');
//==================
//limit/page
        //===================================================
// Set menjadi 0, jika persentase blank string
if($persendecline=='') $persendecline=0;


switch ($method) {
	case 'loadData':
		if ($_SESSION['language'] == 'EN') {
			$ads = "b.namatipe1 as namatipe";
		} else {
			$ads = "b.namatipe as namatipe";
		}
			
		$tex = '';
		

		if ($namaasetsch != '') {
			$tex .= ' AND a.namasset like "%'.$namaasetsch.'%"';
		}
		if ($kodeprojectsch != '') {
			$tex .= ' AND a.kodeproject like "%'.$kodeprojectsch.'%"';
		}
	
		if ($kodeasetsch != '') {
			$tex .= ' AND a.kodeasset like "%'.$kodeasetsch.'%"';
		}
		if ($kodeorgsch != '') {
			$tex .= ' AND a.kodeorg = "'.$kodeorgsch.'"';
		}
		if ($kodeasetlamasch != '') {
			$tex .= ' AND a.kodeassetlama = "'.$kodeasetlamasch.'"';
		}
		if ($tipesch != '') {
			$tex .= ' AND a.tipeasset = "'.$tipesch.'"';
		}
		if ($tipelokasiassetsch != '') {
			$tex .= ' AND a.tipelokasi = "'.$tipelokasiassetsch.'"';
		}
		if ($subsch != '') {
			$tex .= ' AND a.subtipe = "'.$subsch.'"';
		}
		if ($bulanawalsch != '') {
			$tex .= ' AND a.awalpenyusutan = "'.$bulanawalsch.'"';
		}
		if ($posisiassetsch != '') {
			$tex .= ' AND a.posisiasset = "'.$posisiassetsch.'"';
		}
		if ($statussch != '') {
			$tex .= ' AND a.status = "'.$statussch.'"';
		}
		
			// echo $tex;
		$arrtipe=getOrgDetail(1);
		foreach($arrtipe as $kei=>$fal){
			$inorg[$kei]=$kei;
		}
		$limit=20;
		$page=0;
		  if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		  }
		  $offset=$page*$limit;
		//===========================
				$str="select a.*		  
						  from ".$dbname.".sdm_daftarasset a
						  where kodeorg in ('".implode("','",$inorg)."')
						  ".$tex;
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);  
				$numrows=owlBaris($res);
				$jlhbrs=$numrows;
		
		
		$str="select a.*,".$ads." from ".$dbname.".sdm_daftarasset a left join  ".$dbname.".sdm_5tipeasset b on a.tipeasset=.b.kodetipe where 1=1 and kodeorg in ('".implode("','",$inorg)."') ".$tex." order by tanggalperolehan desc,awalpenyusutan desc,namatipe asc,kodeasset desc
		limit ".$offset.",".$limit;
		// echo $str;
		// exit();
		$no = $offset;
		$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$whr = "id='".$bar->status."'";
			$optjns = makeOption($dbname, 'keu_5jenisdisposalasset', 'id,keterangan', $whr);
			$opttipelokasiasset = makeOption($dbname, 'keu_5tipelokasiasset', 'kodelokasi,namalokasi', "kodelokasi='".$bar->tipelokasi."'");
			$no += 1;
			echo "<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".$bar->kodeorg."</td>
			<td>".$bar->posisiasset."</td>
			<td>".$opttipelokasiasset[$bar->tipelokasi]."</td>
			<td>".$bar->namatipe."</td>
			<td>".$bar->kodeasset."</td>
			<td>".$bar->kodeassetlama."</td>
			<td>".$bar->namasset."</td>
			<td align=right>".tanggalnormal($bar->tanggalperolehan)."</td>
			<td>".$optjns[$bar->status]."</td>
			<td align=right>".number_format($bar->hargaperolehan, 2, '.', ',')."</td>
			<td align=right>".$bar->jlhblnpenyusutan."</td>
			<td align=right>".$bar->persendecline."</td>
			<td align=center>".($bar->awalpenyusutan)."</td>
			
			<td align=center>".tanggalnormal($bar->tanggaldisposal)."</td>
			<td>".$bar->nomesin."</td>
			<td>".$bar->norangka."</td>
			<td>".$bar->keterangan."</td>
			<td>".$kamusleasing[$bar->leasing]."</td>
			<td>".$bar->kodeproject."</td>
			<!--<td align=center>".$bar->tipelokasi."</td>-->
			<td align=center>
			<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editAsset('".$bar->kodeorg."','".$bar->tipeasset."','".$bar->kodeasset."','".$bar->namasset."','".$bar->kodebarang."','".tanggalnormal($bar->tanggalperolehan)."','".$bar->status."','".number_format($bar->hargaperolehan,0)."','".$bar->jlhblnpenyusutan."','".($bar->awalpenyusutan)."','".$bar->keterangan."','".$bar->leasing."','".$bar->penambah."','".$bar->pengurang."','".$bar->refbayar."','".$bar->dokpengadaan."','".$bar->persendecline."','".$bar->posisiasset."','".$bar->induk."','".$bar->subtipe."','".$bar->jenis_biaya."','".tanggalnormal($bar->tanggaldisposal)."','".$bar->kodeassetlama."','".$bar->tipelokasi."','".$bar->nomesin."','".$bar->norangka."','".$bar->tipemodel."','".$bar->akumulasiadjust."');\">";
			//echo $folder.$bar->kodeasset.".png";
			if(file_exists($folder.$bar->kodeasset.".png")){
				echo"&nbsp;<img src='images/skyblue/zoom.png' class='resicon' onclick=\"viewfile('event','".$bar->kodeasset.".png')\">";
			}
			
			echo"&nbsp <!--<img src=images/application/application_delete.png class=resicon  title='delete' onclick=\"delAsset('".$bar->kodeorg."','".$bar->kodeasset."');\">-->
			</td>
			</tr>
			</tr>";
		}
		echo "<tr><td colspan=21 align=center>
		".(($page * $limit) + 1)." to ".(($page + 1) * $limit)." Of ".$jlhbrs."
		<br>
		<button class=mybutton onclick=loadData(".($page - 1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=loadData(".($page + 1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
	break;
	case 'update':
		if (($jumlahbulan == '') || ($jumlahbulan == '0')) {
			exit("warning : ".$_SESSION['lang']['jumlahbulanpenyusutan']." ".$_SESSION['lang']['notifemptyzero']);
		}
		if ($optTpasset[$tipe] == 'double') {
			if (($persendecline == '') || ($persendecline == '0')) {
				exit("warning : ".$_SESSION['lang']['notifprosentase']);
			}
		}

		// Lakukan rebonding (rounddown) agar nilai tidak kriting
		$bulanan = floor($bulanan);

		$str = "update ".$dbname.".sdm_daftarasset set
			tipeasset='".$tipe."',
			kodebarang='".$kodebarang."',
			namasset='".$namaaset."',
			tanggalperolehan='".$tanggalperolehan."',
			status=".$status.",
			leasing=".$leasing.",
			hargaperolehan=".$nilaiperolehan.",
			jlhblnpenyusutan=".$jumlahbulan.",
			awalpenyusutan='".$bulanawal."',
			keterangan='".$keterangan."',
			user=".$_SESSION['standard']['userid'].",
			bulanan=".$bulanan.",
			penambah=".$penambah.",
			pengurang=".$pengurang.",
			refbayar='".$refbayar."',
			dokpengadaan='".$nodokpengadaan."',
			persendecline=".$persendecline.",
			posisiasset='".$posisiasset."',
			induk='".$induk."',
			subtipe='".$sub."',
			jenis_biaya='".$jenisbiaya."',
			tanggaldisposal='".$tanggaldisposal."',
			kodeassetlama='".$kodeasetlama."',
			tipelokasi='".$tipelokasiasset."',
			nomesin='".$nomesin."',
			norangka='".$norangka."',
			tipemodel='".$tipemodel."'
			where kodeasset='".$kodeasset."' and kodeorg='".$kodeorg."'";

		try {
			$owlPDO->exec($str);
			$filename = $folder.$kodeasset.".png";
			if (!file_exists($folder)) {
				mkdir($folder, 0777, true);
			}		
			if(file_exists($filename)){
				unlink($filename);
			}
			$file_name=$kodeasset.".png";
			$file_name=$folder.$file_name;
			$qrcode="Kode Asset : ".$kodeasset."\nPemilik Asset : ".$kodeorg."\nPosisi Asset : ".$posisiasset."\nTipe Asset : ".$tipe."\nNama Asset : ".$namaaset;
			QRcode::png($qrcode,$file_name);
			/*header("Content-type: image/png");
			$imgPath = $file_name;
			$image = imagecreatefrompng($imgPath);
			$color = imagecolorallocate($image, 0, 0, 0);
			$string = $kodeasset;
			$fontSize = 2;
			$x = 15;
			$y = 77;
			imagestring($image, $fontSize, $x, $y, $string, $color);
			imagepng($image,$file_name);
			imagedestroy($image);*/
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
	case 'insert':
		if (strlen($tipe) == 4)
			$kodeasset = str_pad($kodeasset, 6, "0", STR_PAD_LEFT);
		else if (strlen($tipe) == 3)
			$kodeasset = str_pad($kodeasset, 7, "0", STR_PAD_LEFT);
		else if (strlen($tipe) == 2)
			$kodeasset = str_pad($kodeasset, 8, "0", STR_PAD_LEFT);
		else
			$kodeasset = str_pad($kodeasset, 8, "0", STR_PAD_LEFT);
		if (($jumlahbulan == '') || ($jumlahbulan == '0')) {
			exit("error: ".$_SESSION['lang']['jumlahbulanpenyusutan']." ".$_SESSION['lang']['notifemptyzero']);
		}
		if ($optTpasset[$tipe] == 'double') {
			if (($persendecline == '') || ($persendecline == '0')) {
				exit("warning : ".$_SESSION['lang']['notifprosentase']);
			}
		}

		// Lakukan rebonding (rounddown) agar nilai tidak kriting
		$bulanan = floor($bulanan);

		//$kodeasset=$_SESSION['org']['kodeorganisasi']."-".$tipe.$kodeasset;
		$str = "insert into ".$dbname.".sdm_daftarasset (
			tipeasset,
			kodeorg,
			kodebarang,
			namasset,
			tanggalperolehan,
			status,
			hargaperolehan,
			jlhblnpenyusutan,
			awalpenyusutan,
			keterangan,
			kodeasset,
			user,
			bulanan,
			leasing,
			penambah,
			pengurang,
			refbayar,
			dokpengadaan,
			persendecline,
			posisiasset,
			induk,
			subtipe,
			jenis_biaya,
			tanggaldisposal,
			kodeassetlama,
			tipelokasi,
			nomesin,
			norangka,
			tipemodel,
			akumulasiadjust
			)
			values(
			'".$tipe."',
			'".$kodeorg."',
			'".$kodebarang."',
			'".$namaaset."',
			'".$tanggalperolehan."',
			'".$status."',
			'".$nilaiperolehan."',
			'".$jumlahbulan."',
			'".$bulanawal."',
			'".$keterangan."',
			'".$kodeasset."',
			'".$_SESSION['standard']['userid']."',
			'".$bulanan."',
			'".$leasing."',
			'".$penambah."',
			'".$pengurang."',
			'".$refbayar."',
			'".$nodokpengadaan."',
			'".$persendecline."','".$posisiasset."',
			'".$induk."','".$sub."','".$jenisbiaya."','".$tanggaldisposal."','".$kodeasetlama."','".$tipelokasiasset."','".$nomesin."','".$norangka."',
			'".$tipemodel."','".$penyusutantambahan."')";
		try {
			$owlPDO->exec($str);
			if (!file_exists($folder)) {
				mkdir($folder, 0777, true);
			}
			$file_name=$kodeasset.".png";
			$file_name=$folder.$file_name;
			$qrcode="Kode Asset : ".$kodeasset."\nPemilik Asset : ".$kodeorg."\nPosisi Asset : ".$posisiasset."\nTipe Asset : ".$tipe."\nNama Asset : ".$namaaset;
			QRcode::png($qrcode,$file_name);
			/*header("Content-type: image/png");
			$imgPath = $file_name;
			$image = imagecreatefrompng($imgPath);
			$color = imagecolorallocate($image, 0, 0, 0);
			$string = $kodeasset;
			$fontSize = 2;
			$x = 2;
			$y = 74;
			imagestring($image, $fontSize, $x, $y, $string, $color);
			imagepng($image,$file_name);
			imagedestroy($image);*/
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
	case'viewfile':
		$tab="";
		$tab.="<img src='".$folder.$namafile."' >";
		
		echo $tab;
	break;
	case 'delete':
		$str = "delete from ".$dbname.".sdm_daftarasset  where kodeasset='".$kodeasset."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: ".$e->getMessage()."\n";
			die();
		}
	break;
	default:
	
	break;
}
		
?>