<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kode=checkPostGet('kode','');
$nama=checkPostGet('nama','');
$method=checkPostGet('method','');


switch($method)
{
	case 'insert':
	
		$str="insert into ".$dbname.".pabrikasi_5kelompokrab (`kode`,`nama`,`updateby`)
		values ('".$kode."','".$nama."','".$_SESSION['standard']['userid']."')";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case 'update':
		$str="update ".$dbname.".pabrikasi_5kelompokrab set nama='".$nama."' where kode='".$kode."'";
		try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
	
	case 'delete':
		
		$str="delete from ".$dbname.".pabrikasi_5kelompokrab where kode='".$kode."'";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
        

	case'loaddata':
		echo"
			<table class=sortable cellspacing=1 border=0>
			 <thead>
				 <tr class=rowheader>
					<td align=center>No</td>
					<td align=center>".$_SESSION['lang']['kode']."</td>
					<td align=center>".$_SESSION['lang']['nama']."</td>    
					<td align=center>".$_SESSION['lang']['action']."</td>
				 </tr>
				</thead>
				<tbody>";
		

		$str="select * from ".$dbname.".pabrikasi_5kelompokrab";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no+=1;
			echo "<tr class=rowcontent>";
			echo "<td align=center>".$no."</td>";
			echo "<td align=left>".$bar['kode']."</td>";
			echo "<td align=left>".$bar['nama']."</td>";
			echo "<td align=center>
				<img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar['kode']."','".$bar['nama']."');\">
				<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kode']."');\">
			  </td>";
			echo "</tr>";
		}
    break;            
                
		
	
	case 'getperiodesort':
	//exit("Error:MASUK");
		$optpersort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$aper = "SELECT distinct substr(tanggal,1,7) as tanggal FROM ".$dbname.".pabrik_5hargatbs where substr(tanggal,1,7) order by tanggal desc";
		//exit ("Error:$asup");
		//$bper=mysql_query($aper) or die(mysql_error($conn));
		//while($cper=mysql_fetch_assoc($bper))
                $bper=$owlPDO->query($aper) or die(print " Gagal: ".PDOException::getMessage());
                $bper->setFetchMode(PDO::FETCH_ASSOC);
                while($cper=$bper->fetch())
		{
			$optpersort.="<option value='".$cper['tanggal']."'>".$cper['tanggal']."</option>";
		}
		echo $optpersort;
	break;
	
	case 'getsuppsort':
			//exit("Error:xx");
		$optsupsort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$asup = "SELECT distinct kodesupplier FROM ".$dbname.".pabrik_5hargatbs ";
		//exit ("Error:$asup");
		//$bsup=mysql_query($asup) or die(mysql_error($conn));
		//while($csup=mysql_fetch_assoc($bsup))
                $bsup=$owlPDO->query($asup) or die(print " Gagal: ".PDOException::getMessage());
                $bsup->setFetchMode(PDO::FETCH_ASSOC);
                while($csup=$bsup->fetch())
		{
			$optsupsort.="<option value='".$csup['kodesupplier']."'>".$namasupp[$csup['kodesupplier']]."</option>";
		}
		echo $optsupsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	case 'getorgsort':
			//exit("Error:xx");
		$optorgsort="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$aorg = "SELECT distinct kodeorg FROM ".$dbname.".pabrik_5hargatbs ";
		//exit ("Error:$aorg");
		//$borg=mysql_query($aorg) or die(mysql_error($conn));
		//while($corg=mysql_fetch_assoc($borg))
                $borg=$owlPDO->query($aorg) or die(print " Gagal: ".PDOException::getMessage());
                $borg->setFetchMode(PDO::FETCH_ASSOC);
                while($corg=$borg->fetch())
		{
			$optorgsort.="<option value='".$corg['kodeorg']."'>".$namaorg[$corg['kodeorg']]."</option>";
		}
		echo $optorgsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	
default:
}
?>