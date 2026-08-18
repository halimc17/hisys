<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('config/connection.php');

$proses = checkPostGet('proses','');
$regional = checkPostGet('regional','');
$periode = checkPostGet('periode','');
$harga = checkPostGet('harga','');

switch($proses) 
{
	case'loadData':
	$no=0;	 
	$strx="select * from ".$dbname.".sdm_5Regionalbbm order by regional asc";
	$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res);
	while($bar=$res->fetch())
	{
		$strchk="select tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$bar->regional."') and tutupbuku='0' and periode='".$bar->periode."'";
		//print_r($strchk);
		$reschk=fetchdata($strchk);
		$conckh=count($reschk);
		//exit("error : ".$conckh);
		if($conckh < 1){
			$show="";
		}
		else $show="<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->regional."','".$bar->periode."','".$bar->rupiah."');\"> ";
	  $no+=1;	
	  echo"<tr class=rowcontent>
	           <td align=center>".$no."</td>
	           <td>".$bar->regional."</td>
	  		     <td>".$bar->periode."</td>
	           <td>".$bar->rupiah."</td>
	           <td align=center>
	              ".$show."
	           </td>
	       </tr>";	
	                  /*<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delRegionalbbm('".$bar->regional."','".$bar->periode."');\">*/
}     
	break;
	case'getharga':
		$gharga='';
		$bulan= (int)(substr($periode, -2));
		$tahun= (int)(substr($periode, 0, 4));
		if($bulan<12)
		{
			$bulan-=1;
		}
		else
		{
			$bulan=12;
			$tahun-=1;
		}
		if($bulan < 10)
		{
			$periode=$tahun."-0".$bulan;
		}
		else
		{
			$periode=$tahun."-".$bulan;
		}
		$strx="select rupiah from ".$dbname.".sdm_5Regionalbbm where regional='".$regional."' and periode='".$periode."' ";
		$res2=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$res2->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res2);
		while($bar=$res2->fetch())
		{
		  
		  $gharga=$bar->rupiah;
		}
		if($gharga =='')
		{
			$gharga=0;
		}
  		//exit("error :".$gharga);
		echo "".$gharga."";

	break;
	case 'delete':
	    $strx = "delete from " . $dbname . ".sdm_5Regionalbbm where regional='" . $regional . "' and periode='".$periode."'";
	    break;
	  case 'update':
	    $strx = "update " . $dbname . ".sdm_5Regionalbbm set rupiah='" . $harga . "' where regional='".$regional."' and periode='".$periode."'";
	    break;
	  case 'insert':
	  	$strchk="select * from ".$dbname.".setup_periodeakuntansi where kodeorg in (select kodeunit from ".$dbname.".bgt_regional_assignment where regional='".$regional."') and tutupbuku='0' and periode='".$periode."'";
		//exit("error : ".$strchk);
		//print_r($strchk);
		$reschk=fetchdata($strchk);
		$conckh=count($reschk);
		if($conckh < 1){
			exit(" Gagal !: Periode ".$periode." Regional ".$regional." sudah tutup buku atau belum terdapat dalam periode akuntansi");
		}
	      $strx = "insert into " . $dbname . ".sdm_5Regionalbbm(regional,periode,rupiah)
	               values('" . $regional . "','" . $periode . "','" . $harga . "')";
	    break;
	  default:
	    break;
}

try {
  $owlPDO->exec($strx);
} catch (PDOException $e) {
  print " Gagal  !: " . $e->getMessage() . "\n";
  die();
}



?>