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

$method     = checkPostGet('method','');
$unit       = checkPostGet('unit','');
$divisi     = checkPostGet('divisi','');
$karyawan   = checkPostGet('karyawan','');
$tph        = checkPostGet('tph','');
$tipe       = checkPostGet('tipe','');
$jenis      = checkPostGet('jenis','');
$method     = checkPostGet('method','');
$blok       = checkPostGet('blok','');
$mandor     = checkPostGet('mandor','');
$tinggi     = checkPostGet('tinggi','');
$lebar      = checkPostGet('lebar','');
$orientation= checkPostGet('orientation','');
$maxkolom   = checkPostGet('maxkolom','');
$ukkertas   = checkPostGet('ukkertas','');
switch ($method) {
	 case'generateqrcode':
		switch ($jenis) {
			case'karyawanx':
				$folder      = "fileupload/qrcodekaryawan/";
				//exit('Error :'.$folder);
				if (!file_exists($folder)) {
					mkdir($folder, 0777, true);
				}
				$where='';
				if($karyawan!=''){$where.=" and karyawanid='".$karyawan."'";}
				if($unit!=''){$where.=" and lokasitugas='".$unit."'";}
				if($divisi!=''){$where.=" and subbagian='".$divisi."'";}
				$str = "select * from " . $dbname . ".datakaryawan where 1=1 ".$where."";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$karyawanid=$bar['karyawanid'];
					$filename = $folder.$karyawanid.".png";
					if(file_exists($filename)){
						unlink($filename);
					}
					$file_name=$karyawanid.".png";
					$file_name=$folder.$file_name;
					$strx="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['karyawanid']."'";
					$resx=fetchData($strx);
					$karyawanid = $resx[0]['karyawanid'];
					
					
					$qrcode=$karyawanid;
					QRcode::png($qrcode,$file_name);
				}
			break;
			case'tphx':
				$folder      = "images/qrcode/tph/";
				if (!file_exists($folder)) {
					mkdir($folder, 0777, true);
				}
				$where='';
				if($tph!=''){$where.=" and kode = '".$tph."'";}
				if($divisi!=''){$where.=" and kodeorg like '".$divisi."%'";}
				if($blok!=''){$where.=" and kodeorg like '".$blok."%'";}
				$str = "select * from " . $dbname . ".kebun_5tph where 1=1 ".$where.""; #exit('error'.$str);
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while ($bar = $res->fetch()) {
					$kode = $bar['kode'];
					$file_name=$kode.".png";
					$file_name=$folder.$file_name;
					if(file_exists($file_name)){
						unlink($file_name);
					}
					if(!file_exists($file_name)){
						QRcode::png($kode,$file_name,QR_ECLEVEL_H,100);
						
						header("Content-type: image/png");
						$imgPath = $file_name;
						$image = imagecreatefrompng($imgPath);
						$color = imagecolorallocate($image, 0, 0, 0);
						$string = $kode;
						$string = "";
						$fontSize = 20;
						$x = 200;
						$y = 950;
						imagestring($image, $fontSize, $x, $y, $string, $color);
						
						imagepng($image,$file_name);
						imagedestroy($image);
					}
				}
			break;
		}
	 break;
	  case'getdivisi':
		$str = "select * from " . $dbname . ".organisasi where induk='".$unit."' and tipe not like '%GUDANG%' order by namaorganisasi asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - ".$bar['namaorganisasi']."</option>";
		}
		echo $opttipe;
	 break;
	 case'getkar':
		$i='';
		if($mandor!=''){
			$i=" and kodemandor='".$mandor."'";
		}
		$str = "select * from " . $dbname . ".datakaryawan where lokasitugas='".$unit."' and subbagian='".$divisi."' and (tanggalkeluar='' or tanggalkeluar='0000-00-00') ".$i." order by namakaryawan asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['karyawanid'] . ">[" . $bar['nik'] . "] - ".$bar['namakaryawan']."</option>";
		}
		echo $opttipe;
	 break;
	 case'getmandor':
		$str = "select distinct(kodemandor) as mdr from " . $dbname . ".datakaryawan where lokasitugas='".$unit."' and subbagian='".$divisi."' and (tanggalkeluar='' or tanggalkeluar='0000-00-00') order by namakaryawan asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage()); //exit('error'.$str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttipe="<option value=''>".$_SESSION['lang']['all']."</option>";
		$nmmdr=makeOption($dbname,'kebun_5kemandoran','kodemandor,namamandor');
		while ($bar = $res->fetch()) {
			$opttipe.="<option value=" . $bar['mdr'] . ">" . $bar['mdr'] . " - ".$nmmdr[$bar['mdr']]."</option>";
		}
		echo $opttipe;
	 break;
	 case'gettph':
		$str = "select * from " . $dbname . ".kebun_5tph where kodeorg like '".$divisi."%' order by kode asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttph="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
		    $opttph.="<option value=" . $bar['kode'] . ">" . $bar['kode'] . "</option>";
		}
		echo $opttph;
	 break;
	 case'getblok':
		$where=" and kodeorg like '".$unit."%'";
		if($divisi!=''){
			$where.=" and kodeorg like '".$divisi."%'";
		}
		$str = "select * from " . $dbname . ".setup_blok where 1=1 ".$where." order by kodeorg asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$opttph="<option value=''>".$_SESSION['lang']['all']."</option>";
		while ($bar = $res->fetch()) {
		    $opttph.="<option value=" . $bar['kodeorg'] . ">" . getNamaOrg($bar['kodeorg']) . "</option>";
		}
		
		echo $opttph;
		
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
	 case'karyawanx':
		$folder      = "fileupload/qrcodekaryawan/";
        $tab = "";
		$where='';
		if($karyawan!=''){$where.=" and karyawanid='".$karyawan."'";}
		if($unit!=''){$where.=" and lokasitugas='".$unit."'";}
		if($divisi!=''){$where.=" and subbagian='".$divisi."'";}
		$str = "select * from " . $dbname . ".datakaryawan where 1=1 ".$where." and tipekaryawan in ('2','3','4')";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$cont=$path=0;
		if($tipe=='pdf'){
			$max=4;
			$tab.="<div align=center><b><font size=20>MASTER QRCODE DATA KARYAWAN DIVISI ".$divisi."</font></b></div><br>";
			$tab.= "<table class=data border=1 cellspacing=1><tbody>";
		}else{
			$max=7;
			$tab.= "<table class=sortable border=1 cellspacing=0 border=1><tbody>";
		}
		$tab.= "<tr>";
		while ($bar = $res->fetch()) {
			$cont+=1;
			if(file_exists($folder.$bar['karyawanid'].".png")){
				$path="<img src='".$folder.$bar['karyawanid'].".png' style='width:160px;height:150px;'>";
				$attr="";
				$tab.="<td width='35' align='center'>".$path."<br>".$bar['karyawanid']."<br>".$bar['subbagian']."<br>".$bar['namakaryawan']."</td>";
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
            $dompdf->stream("datakaryawan",array("Attachment"=>0));
		} else if($tipe=='html') {
			echo $tab;
			//exit('Error :'.$tab);
		}
	break;
	
	case'tphx':
		$folder      = "images/qrcode/tph/";
        $tab = "";
		$where='';
		if($divisi!=''){$where.=" and kodeorg like '".$divisi."%'";}
		if($tph!=''){$where.=" and kode = '".$tph."'";}
		if($blok!=''){$where.=" and kodeorg = '".$blok."'";}
		
		$str = "select * from " . $dbname . ".kebun_5tph where 1=1 ".$where." order by kode asc";// exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$contx=0;
		$cont=$path=0; $i='';
		if($blok!=''){
			$i=" BLOK ".getNamaOrg($blok)."";
		}
		if($tipe=='pdf'){
			$max=8;
			$tab.="<table width=100%>
				<tr>
					<td align=center>
						<b><font size=13>MASTER QRCODE TPH DIVISI ".getNamaOrg($divisi)." ".$i."</font></b>
					</td>
					<td width=65px ></td>
					<td  align=center>
						<b><font size=13>MASTER QRCODE TPH DIVISI ".getNamaOrg($divisi)." ".$i."</font></b>
					</td>
				</tr>
			</table>";
			$tab.= "<table class=sortable border=1 cellspacing=6><tbody>";
			$x=4;
		}else{
			$max=8;
			$tab.= "<table class=sortable border=1 cellspacing=0><tbody>";
		}
		
		$tab.= "<tr>";
		$z=0;
		while ($bar = $res->fetch()) {
			$cont+=1;
			$contx+=1;
			if(file_exists($folder.$bar['kode'].".png")){
				if($tipe=='pdf'){
					$path="<img src='".$folder.$bar['kode'].".png' style='width:110px;height:100px;'>";
					$attr="";
					if($contx==$x || $contx-$z==8){
						$z=$contx;
						$tab.="<td align='center' vertical-align=center style=\"border-top:1px #000 solid;border-bottom:1px #000 solid;border-left:1px #000 solid;border-right:1px #000 solid;\">".$path."</td>";	
						$tab.="<td align='center' width=65px style=\"border-top:0px;border-bottom:0px\">&nbsp;</td>";

					}else{
						$tab.="<td align='center' vertical-align=center style=\"border-top:1px #000 solid;border-bottom:1px #000 solid;border-left:1px #000 solid;border-right:1px #000 solid;\">".$path."</td>";					
					}
				}else{
					$path="<img src='".$folder.$bar['kode'].".png' style='width:110px;height:100px;'>";
					$attr="";
					$tab.="<td align='center' vertical-align=center style=\"border-top:1px #000 solid;border-bottom:1px #000 solid;border-left:1px #000 solid;border-right:1px #000 solid;\">".$path."</td>";				
				}
			}
			if($cont==$max){
				$tab.= "</tr><tr>";
				$cont=0;
				$contx=0;
			}
		}
		$tab.= "</tr>";
		$tab.="</tbody></table>";
		
		if($tipe=='pdf'){
			// $pdf->Output();
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("TPH",array("Attachment"=>0));
			
		} else if($tipe=='html') {
			echo $tab;
		}
	break;
	case'tphx2':
		$folder      = "images/qrcode/tph/";
        $tab = "";
		$where='';
		if($divisi!=''){$where.=" and kodeorg like '".$divisi."%'";}
		if($tph!=''){$where.=" and kode = '".$tph."'";}
		if($blok!=''){$where.=" and kodeorg = '".$blok."'";}
		$str = "select * from " . $dbname . ".kebun_5tph where 1=1 ".$where." order by kode asc";// exit('error'.$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$contx=0;
		$cont=$path=0; $i='';
		if($blok!=''){
			$i=" BLOK ".getNamaOrg($blok)."";
		}

		if($lebar==''){$lebar=0;}
		if($tinggi==''){$tinggi=0;}

		if($tipe=='pdf'){
			if($maxkolom==''){$maxkolom=0;}
			if($maxkolom!=0){
				$max=$maxkolom;
			}else{
				if($lebar!='0' and $tinggi!='0'){
					switch($ukkertas){
						case'A4':
							if($orientation=='portrait'){
								$max=floor(650/$lebar);
							}else{
								$max=floor(950/$lebar);
							}					
						break;
						case'A5':
							if($orientation=='portrait'){
								$max=floor(475/$lebar);
							}else{
								$max=floor(650/$lebar);
							}
						break;
						case'A6':
							if($orientation=='portrait'){
								$max=floor(350/$lebar);
							}else{
								$max=floor(400/$lebar);
							}
						break;
						case'A3':
							if($orientation=='portrait'){
								$max=floor(897/$lebar);
							}else{
								$max=floor(1250/$lebar);
							}
						break;
						case'Ledger':
							if($orientation=='portrait'){
								$max=floor(1300/$lebar);
							}else{
								$max=floor(800/$lebar);
							}
						break;
						case'Letter':
							if($orientation=='portrait'){
								$max=floor(600/$lebar);
							}else{
								$max=floor(850/$lebar);
							}
						break;
						case'Legal':
							if($orientation=='portrait'){
								$max=floor(650/$lebar);
							}else{
								$max=floor(1100/$lebar);
							}
						break;
						case'Executive':
							if($orientation=='portrait'){
								$max=floor(510/$lebar);
							}else{
								$max=floor(800/$lebar);
							}
						break;
					}
				}else{
					$max=2;
				}
			}
			$tab.= "<table class=sortable border=0 cellspacing=15><tbody>";
			$x=5;
		}else{
			$max=8;
			$tab.= "<table class=sortable border=1 cellspacing=0><tbody>";
		}
		
		$tab.= "<tr>";
		$z=0;
		
		if($lebar!='0' and $tinggi!='0'){
			$style="style='width:".$lebar."px;height:".$tinggi."px;'";
			$n=($lebar*18)/100;
			$n=($lebar*10)/100;
			if($n<12){$n=12;}
		}else{
			$style="style='width:350px;height:350px;'";
			$n=10;
		}
		
		while ($bar = $res->fetch()) {
			$cont+=1;
			$contx+=1;
			if(file_exists($folder.$bar['kode'].".png")){
				$path="<img src='".$folder.$bar['kode'].".png' ".$style.">";
				$attr="";
				$tab.="<td align='center' vertical-align=center style=\"border-top:1px #000 solid;border-bottom:1px #000 solid;border-left:1px #000 solid;border-right:1px #000 solid;\"><font size=".$n.">".getNamaOrg(substr($bar['kode'],0,10))."<b>-".substr($bar['kode'],-2)."</b></font><br>".$path."</td>";					
			}
			if($cont==$max){
				$tab.= "</tr><tr>";
				$cont=0;
			}
		}
		$tab.= "</tr>";
		$tab.="</tbody></table>";
		
		// echo $tab;
		if($tipe=='pdf'){
			// $pdf->Output();
			$dompdf = new Dompdf();
            $dompdf->loadHtml($tab);
            $dompdf->setPaper($ukkertas, $orientation);
            $dompdf->render();
            $dompdf->stream("TPH",array("Attachment"=>0));
			
		} else if($tipe=='html') {
			echo $tab;
		}
	break;
	
}
?>