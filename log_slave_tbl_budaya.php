<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kode=checkPostGet('kode','');
$kodeorg=checkPostGet('kodeorg','');
$budidaya=checkPostGet('budidaya','');
$method=checkPostGet('method','');

switch($method){
	case 'delete':
		$strx="delete from ".$dbname.".kebun_5budidaya where kode='".$kode."'";
		
	break;
	case 'update':
	$strx="update ".$dbname.".kebun_5budidaya set kodeorg='".$kodeorg."',budidaya='".$budidaya."' where kode='".$kode."'";
	break;	
	case 'insert':
	/*print_r($_POST);
	exit();*/
	$strx="insert into ".$dbname.".kebun_5budidaya(
				   kode,kodeorg,budidaya)
			values('".$kode."','".$kodeorg."','"
					.$budidaya."')";
					//echo $strx; exit();
	break;
	default:
    break;	
}
 try{$owlPDO->exec($strx); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	

//ambil data dari tabel 
 		
	$srt="select * from ".$dbname.".kebun_5budidaya order by kode desc";  //echo $srt;
	$rep=$owlPDO->query($srt) or die(print " Gagal: ".PDOException::getMessage());
	$rep->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$rep->fetch())
		{
		//get akun
		$spr="select * from  ".$dbname.".organisasi where `kodeorganisasi`='".$bar->kodeorg."'";
		$rej=$owlPDO->query($spr) or die(print " Gagal: ".PDOException::getMessage());
		$rej->setFetchMode(PDO::FETCH_OBJ);

		$bas=$rej->fetch();
		$no+=1;
		echo"<tr class=rowcontent>
			  <td>".$no."</td>
			  <td>".$bas->kodeorganisasi."</td>
			  <td>".$bas->namaorganisasi."</td>
			  <td>".$bar->kode."</td>
			  <td>".$bar->budidaya."</td>
			  <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->kode."','".$bar->kodeorg."','".$bar->budidaya."');\"></td>
			  <td><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delTbldya('".$bar->kode."');\"></td>
			 </tr>";
		}
 ?>