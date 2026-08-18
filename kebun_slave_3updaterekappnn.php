<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));


$blokinput=checkPostGet('blokinput','');
$tglinput=checkPostGet('tglinput','');
$hkinput=checkPostGet('hkinput','');
$jjginput=checkPostGet('jjginput','');
$bjrinput=checkPostGet('bjrinput','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');




	





$stream.="<tbody></table>";
switch($proses){
######update
	
	case'savedata':
		if($jjginput>0){
			$str="update ".$dbname.".kebun_rekappnn set jjgpanen='".$jjginput."',tenagakerja='".number_format($hkinput,2)."',kgkebun='".($bjrinput*$jjginput)."'
				where divisi='".substr($blokinput,0,6)."' and blok='".$blokinput."' and tanggal='".$tglinput."' ";
			try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
		}

	break;
	
	
######PREVIEW
    case 'preview':
		
		$border='border=0';


		$str = "select kodeorg,tanggal, sum(hasilkerja) as hasilkerja, sum(hkpanenperhari) as hk 
						from ".$dbname.".kebun_prestasi_vs_hk where unit='".$unit."' 
						and tanggal between '".$tgl1."' and '".$tgl2."' group by kodeorg,tanggal";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrtgl[$bar['tanggal']]=$bar['tanggal'];
			$arrblok[$bar['kodeorg']]=$bar['kodeorg'];
			$listblok[$bar['tanggal']][$bar['kodeorg']]=$bar['kodeorg'];
			$jjgkegpnn[$bar['tanggal']][$bar['kodeorg']]=$bar['hasilkerja'];
			$hkkegpnn[$bar['tanggal']][$bar['kodeorg']]=$bar['hk'];
		}

		$str = "select * from 
				".$dbname.".kebun_rekappnn_vw where divisi  like '".$unit."%' 
				and tanggal between '".$tgl1."' and '".$tgl2."'";
		/*
		$str = "select blok,tanggal,sum(jjgpanen) as jjgpanen, sum(tenagakerja) as tenagakerja from 
				".$dbname.".kebun_rekappnn_vw where divisi  like '".$unit."%' 
				and tanggal between '".$tgl1."' and '".$tgl2."' group by blok,tanggal";
		*/		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrtgl[$bar['tanggal']]=$bar['tanggal'];
			$arrblok[$bar['blok']]=$bar['blok'];
			$listblok[$bar['tanggal']][$bar['blok']]=$bar['blok'];
			$jjgrkppnn[$bar['tanggal']][$bar['blok']]=$bar['jjgpanen'];
			$hkrkppnn[$bar['tanggal']][$bar['blok']]=$bar['tenagakerja'];
			$bjrrkppnn[$bar['tanggal']][$bar['blok']]=$bar['bjr'];
			$kgrkppnn[$bar['tanggal']][$bar['blok']]=$bar['kgkebun'];
		}	


		array_multisort($arrtgl,SORT_ASC);
		array_multisort($arrblok,SORT_ASC);

		echo"<pre>";


		$stream="";
		$stream.="<table cellspacing=1 class=sortable cellpadding=1 ".$border.">";
		$stream.="<thead>";
		$stream.="<tr>";
		$stream.="<td align=center>NO</td>";
		$stream.="<td align=center>TANGGAL</td>";
		$stream.="<td align=center>BLOK</td>";
		$stream.="<td align=center>HK REKAP</td>";
		$stream.="<td align=center>JJG REKAP</td>";
		$stream.="<td align=center>BJR REKAP</td>";
		$stream.="<td align=center>KG KEBUN REKAP</td>";
		$stream.="<td align=center>HK PNN</td>";
		$stream.="<td align=center>JJG PNN</td>";
		$stream.="<td align=center>SELISIH HK</td>";
		$stream.="<td align=center>SELISIH JJG</td>";
		$stream.="</tr>";
		$stream.="</thead>";

		foreach($arrtgl as $tgl){
			foreach($arrblok as $blok){
				if(@$listblok[$tgl][$blok]!=''){
					@$no+=1;
					$stream.="<tr class=rowcontent id=row".$no.">";
						$stream.="<td align=center>".$no."</td>";
						$stream.="<td align=left id=tglinput".$no.">".$tgl."</td>";
						$stream.="<td align=left id=blokinput".$no.">".$blok."</td>";
						$stream.="<td align=right>".@$hkrkppnn[$tgl][$blok]."</td>";
						$stream.="<td align=right>".@$jjgrkppnn[$tgl][$blok]."</td>";
						$stream.="<td align=right id=bjrinput".$no.">".@$bjrrkppnn[$tgl][$blok]."</td>";
						$stream.="<td align=right>".@$kgrkppnn[$tgl][$blok]."</td>";
						$stream.="<td align=right id=hkinput".$no.">".@$hkkegpnn[$tgl][$blok]."</td>";
						$stream.="<td align=right id=jjginput".$no.">".@$jjgkegpnn[$tgl][$blok]."</td>";
						$stream.="<td align=right>".number_format(abs(@$hkkegpnn[$tgl][$blok]-@$hkrkppnn[$tgl][$blok]),2)."</td>";
						$stream.="<td align=right>".number_format(abs(@$jjgkegpnn[$tgl][$blok]-@$jjgrkppnn[$tgl][$blok]))."</td>";
					$stream.="</tr>";
				}
			}
		}
		echo"<button class=mybutton onclick=saveall(".$no.");>".$_SESSION['lang']['proses']."</button>";
		$stream.="</table>";
			
			
			
			
				echo $stream;
    break;

   
}
?>