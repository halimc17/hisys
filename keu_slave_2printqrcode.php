<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/terbilang.php');
require('lib/fpdf.php');
require('lib/htmlparser.inc');
require('lib/htmltofpdf.php');
require_once('phpqrcode/qrlib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method      = checkPostGet('method','');
$tipeasset   = checkPostGet('tipeasset','');
$unit        = checkPostGet('unit','');
$posisiasset = checkPostGet('posisiasset','');
$subtipeasset= checkPostGet('subtipeasset','');
$tipe        = checkPostGet('tipe','');
$jenis       = checkPostGet('jenis','');
$klbarang       = checkPostGet('klbarang','');
$subklbarang       = checkPostGet('subklbarang','');
$kodebarang       = checkPostGet('kodebarang','');
switch ($method) {
	 case'generateqrcode':
		switch ($jenis) {
			case'asset':
				$folder      = "fileupload/qrcodeasset/";
				if (!file_exists($folder)) {
					mkdir($folder, 0777, true);
				}
				$where='';
				if($tipeasset!=''){$where.=" and tipeasset='".$tipeasset."'";}
				if($unit!=''){$where.=" and kodeorg='".$unit."'";}
				if($posisiasset!=''){$where.=" and posisiasset='".$posisiasset."'";}
				if($subtipeasset!=''){$where.=" and subtipe='".$subtipeasset."'";}
				$str = "select * from " . $dbname . ".sdm_daftarasset where 1=1 ".$where."";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$kodeasset=$bar['kodeasset'];
					$filename = $folder.$kodeasset.".png";
					if(file_exists($filename)){
						unlink($filename);
					}
					$file_name=$kodeasset.".png";
					$file_name=$folder.$file_name;
					$strx="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='".$bar['tipeasset']."' and kodesub='".$bar['subtipe']."'";
					$resx=fetchData($strx);
					$subtipeasset = $resx[0]['namasub'];
					
					$optNmOrg = makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi");
					$optTipeAsset = makeOption($dbname,"sdm_5tipeasset","kodetipe,namatipe","kodetipe='".$bar['tipeasset']."'");
					
					$qrcode="Kode Asset : ".$kodeasset."\nPemilik Asset : ".$optNmOrg[$bar['kodeorg']]."(".$bar['kodeorg'].")\nPosisi Asset : ".$optNmOrg[$bar['posisiasset']]."(".$bar['kodeorg'].")\nTipe Asset : ".$optTipeAsset[$bar['tipeasset']]."(".$bar['tipeasset'].")\nSub Tipe Asset : ".$subtipeasset."\nNama Asset : ".$bar['namasset'];
					QRcode::png($qrcode,$file_name);
				}
			break;
			case'barang':
				$folder      = "images/qrcode/";
				if (!file_exists($folder)) {
					mkdir($folder, 0777, true);
				}
				$where='';
				if($klbarang!=''){$where.=" and kelompokbarang='".$klbarang."'";}
				if($subklbarang!=''){$where.=" and kodebarang like '".$subklbarang."%'";}
				if($kodebarang!=''){$where.=" and kodebarang like '".$kodebarang."%'";}
				$str = "select * from " . $dbname . ".log_5masterbarang where 1=1 ".$where."";// exit('error'.$str);
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$kodebarang = $bar['kodebarang'];
					$file_name=$kodebarang.".png";
					$file_name=$folder.$file_name;
					if(!file_exists($file_name)){
						QRcode::png($kodebarang,$file_name);
						
						header("Content-type: image/png");
						$imgPath = $file_name;
						$image = imagecreatefrompng($imgPath);
						$color = imagecolorallocate($image, 0, 0, 0);
						$string = $kodebarang;
						$fontSize = 2;
						$x = 20;
						$y = 74;
						imagestring($image, $fontSize, $x, $y, $string, $color);
						
						imagepng($image,$file_name);
						imagedestroy($image);
					}
				}
			break;
		}
	 break;
	 case'getsub':
		$str = "select * from " . $dbname . ".sdm_5subtipeasset where kodetipe='".$tipeasset."' order by namasub asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['kodesub'] . ">" . $bar['kodesub'] . " - ".$bar['namasub']."</option>";
		}
		echo $opttipe;
	 break;
	 case'getsubklbarang':
		$str = "select * from " . $dbname . ".log_5subklbarang where kelompok='".$klbarang."' order by kode asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - ".$bar['namasubkelompok']."</option>";
		}
		echo $opttipe;
	 break;
	 case'getkodebarang':
		$str = "select * from " . $dbname . ".log_5masterbarang where kodebarang like '".$subklbarang."%' and kelompokbarang='".$klbarang."' order by kodebarang asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['kodebarang'] . ">" . $bar['kodebarang'] . " - ".$bar['namabarang']."</option>";
		}
		echo $opttipe;
	 break;
	 case'asset':
		$folder      = "fileupload/qrcodeasset/";
        $tab = "";
		if($tipe=='pdf'){
			$tab.= "<table border='1' ><tbody>";
		}else{
			$tab.= "<table class=data cellpadding=1 cellspacing=1 border=1><tbody>";
		}
		$where='';
		if($tipeasset!=''){$where.=" and tipeasset='".$tipeasset."'";}
		if($unit!=''){$where.=" and kodeorg='".$unit."'";}
		if($posisiasset!=''){$where.=" and posisiasset='".$posisiasset."'";}
		if($subtipeasset!=''){$where.=" and subtipe='".$subtipeasset."'";}
		$str = "select * from " . $dbname . ".sdm_daftarasset where 1=1 ".$where."";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$cont=$path=0;
		if($tipe=='pdf'){$max=3;}else{$max=3;}
		$tab.= "<tr>";
		while ($bar = $res->fetch()) {
			$cont+=1;
			if(file_exists($folder.$bar['kodeasset'].".png")){
				$path="<img src='".$folder.$bar['kodeasset'].".png' style='width:220px;height:210px;'>";
				$attr="";
				$tab.="<td width='35' align='center'>".$path."<br>".$bar['kodeasset']."</td>";
			}else{
				$path="<img src='images/question.png' style='width:220px;height:210px;'>";
				$path="";
				$tab.="<td width='35' align='center'>".$path."<br>".$bar['kodeasset']."</td>";
			}
			if($cont==$max){
				$tab.= "</tr><tr>";
				$cont=0;
			}
		}
		$tab.= "</tr>";
		$tab.="</tbody></table>";
		if($tipe=='pdf'){
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("form checklist",array("Attachment"=>0));
		} else if($tipe=='html') {
			echo $tab;
		}
	break;
	
	case'barang':
		$folder      = "images/qrcode/";
        $tab = "";
		if($tipe=='pdf'){
			$tab.= "<table border='1' ><tbody>";
		}else{
			$tab.= "<table class=data cellpadding=1 cellspacing=1 border=1><tbody>";
		}
		$where='';
		if($klbarang!=''){$where.=" and kelompokbarang='".$klbarang."'";}
		if($subklbarang!=''){$where.=" and kodebarang like '".$subklbarang."%'";}
		if($kodebarang!=''){$where.=" and kodebarang like '".$kodebarang."%'";}
		$str = "select * from " . $dbname . ".log_5masterbarang where 1=1 ".$where."";// exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$cont=$path=0;
		if($tipe=='pdf'){$max=3;}else{$max=4;}
		$tab.= "<tr>";
		while ($bar = $res->fetch()) {
			$cont+=1;
			if(file_exists($folder.$bar['kodebarang'].".png")){
				$path="<img src='".$folder.$bar['kodebarang'].".png' style='width:220px;height:210px;'>";
				$attr="";
				$tab.="<td width='35' align='center'>".$path."</td>";
			}else{
				$path="<img src='images/question.png' style='width:220px;height:210px;'>";
				$path="";
				$tab.="<td width='35' align='center'>".$path."<br>".$bar['kodebarang']."</td>";
			}
			if($cont==$max){
				$tab.= "</tr><tr>";
				$cont=0;
			}
		}
		$tab.= "</tr>";
		$tab.="</tbody></table>";
		
		if($tipe=='pdf'){
			// class PDF extends FPDF{}
			
			// $pdf=new FPDF('L','mm','A4');
			// $pdf->SetAutoPageBreak(false);
			// $pdf->AddPage();
			
			// $path=$folder."31101001.png";
			// $pdf->Image($path,0,0,30,30);
			// $pdf->Cell(70,5,"Telah terima dari",0,0,'L');
			
						
			// $pdf->Output();
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream("form checklist",array("Attachment"=>0));
			
		} else if($tipe=='html') {
			echo $tab;
		}
	break;
	
}
?>