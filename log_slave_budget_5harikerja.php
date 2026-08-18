<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$tahunbudget    =checkPostGet('tahunbudget','');
$kodeorg        =checkPostGet('kodeorg','');
$hrsetahun      =checkPostGet('hrsetahun','');
$hrminggu       =checkPostGet('hrminggu','');
$hrlibur        =checkPostGet('hrlibur','');
$hrliburminggu  =checkPostGet('hrliburminggu','');
$hkeffektif     =checkPostGet('hkeffektif','');
$method         =checkPostGet('method','');
$oldtahunbudget =checkPostGet('oldtahunbudget','');
$oldkodeorg     =checkPostGet('oldkodeorg','');
$unit     		=checkPostGet('unit','');

$jlhcuti = checkPostGet('jlhcuti','');
$s1s2 = checkPostGet('s1s2','');
$h1h2 = checkPostGet('h1h2','');
$p1p3 = checkPostGet('p1p3','');
$mangkir = checkPostGet('mangkir','');

switch($method){
	case'insert':
		$oldtahunbudget==''?$oldtahunbudget=$_POST['tahunbudget']:$oldtahunbudget=$_POST['oldtahunbudget'];
		$sCek="select tahunbudget from ".$dbname.".bgt_hk where tahunbudget='".$oldtahunbudget."' and unit='".$unit."'";
		$rCek = count(fetchdata($sCek));
		if(strlen($tahunbudget)<4){
			exit("Error : Panjang Karakter Kurang");
		}
		if($tahunbudget==''){
			echo "warning : Tahun Budget masih kosong";
			exit();
		}else if ($hrsetahun==''){
			echo "warning : Hari dalam satu tahun masih kosong";
			exit();
		}else if ($hrminggu==''){
			echo "warning : Hari dalam satu minggu masih kosong";
			exit();
		}else if ($hrlibur==''){
			echo "warning : Hari libur masih kosong";
			exit();
		}else if ($hrliburminggu ==''){
			echo "warning : Hari libur minggu masih kosong";
			exit();
		}
		if($rCek>0){
			$sDel="delete from ".$dbname.".bgt_hk where tahunbudget='".$oldtahunbudget."' and unit='".$unit."' ";	   
			try{$owlPDO->exec($sDel); 
				$sDel2="insert into ".$dbname.".bgt_hk (`tahunbudget`,`harisetahun`,`hrminggu`,`hrlibur`,`hrliburminggu`,`updatedby`,`jlhcuti`,`s1s2`,`h1h2`,`p1p3`,`mangkir`,`unit`) 
				values ('".$tahunbudget."','".$hrsetahun."','".$hrminggu."','".$hrlibur."','".$hrliburminggu."','".$_SESSION['standard']['userid']."','".$jlhcuti."','".$s1s2."','".$h1h2."','".$p1p3."','".$mangkir."','".$unit."')";
				try{$owlPDO->exec($sDel2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
				
			}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}else{
			$sIns="insert into ".$dbname.".bgt_hk (`tahunbudget`,`harisetahun`,`hrminggu`,`hrlibur`,`hrliburminggu`,`updatedby`,`jlhcuti`,`s1s2`,`h1h2`,`p1p3`,`mangkir`,`unit`) 
				values ('".$tahunbudget."','".$hrsetahun."','".$hrminggu."','".$hrlibur."','".$hrliburminggu."','".$_SESSION['standard']['userid']."','".$jlhcuti."','".$s1s2."','".$h1h2."','".$p1p3."','".$mangkir."','".$unit."')";
			try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	case'loadData':
		$str="select * from ".$dbname.".bgt_hk  order by tahunbudget desc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$thrlb=$bar['hrminggu']+$bar['hrlibur']-$bar['hrliburminggu'];
			$thke=$bar['harisetahun']-$thrlb;
			$tsim=$bar['s1s2']+$bar['h1h2']+$bar['p1p3']+$bar['mangkir'];
			$tothke=$thke-($bar['jlhcuti']+$tsim);
			$persen=$tothke/$bar['harisetahun']*100;
			
			$no+=1;	
			echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=center>".$bar['unit']."</td>
			<td align=center>".$bar['tahunbudget']."</td>
			<td align=right>".$thke."</td>
			<td align=right>".$bar['harisetahun']."</td>
			<td align=right>".$bar['hrminggu']."</td>
			<td align=right>".$bar['hrlibur']."</td>
			<td align=right>".$bar['hrliburminggu']."</td>
			<td align=right>".$thrlb."</td>
			<td align=right>".($bar['jlhcuti']+$tsim)."</td>
			<td align=right>".$bar['jlhcuti']."</td>
			<td align=right>".$bar['s1s2']."</td>
			<td align=right>".$bar['h1h2']."</td>
			<td align=right>".$bar['p1p3']."</td>
			<td align=right>".$bar['mangkir']."</td>
			<td align=right>".$tsim."</td>
			<td align=right>".$tothke."</td>
			<td align=right>".number_format($persen,2)."</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['tahunbudget']."','".$bar['unit']."');\"></td>
			</tr>";	
		}     
	break;
	case'getData':
			$sDt="select * from ".$dbname.".bgt_hk where tahunbudget='".$tahunbudget."' and unit='".$unit."'  order by tahunbudget desc";
			$res=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$rDet=$res->fetch();
			echo $rDet['tahunbudget']."###".$rDet['harisetahun']."###".$rDet['hrminggu']."###".$rDet['hrlibur']."###".$rDet['hrliburminggu']."###".$rDet['jlhcuti']."###".$rDet['s1s2']."###".$rDet['h1h2']."###".$rDet['p1p3']."###".$rDet['mangkir']."###".$rDet['unit'];
			
			#exit("error");
		break;
		default:
		break;
}
?>