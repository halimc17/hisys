<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kodeorg = checkPostGet('kodeorg','');
$kode = checkPostGet('kode','');
$tahun = checkPostGet('tahun','');
$jumlah = checkPostGet('jumlah','');
$keterangan = checkPostGet('keterangan','');
$method = checkPostGet('method','');		

if($jumlah=='')
   $jumlah=0;

switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_5catu set 
	       jumlah=".$jumlah.",
	       keterangan='".$keterangan."'
	       where kodeorg='".$kodeorg."' and kelompok='".$kode."'
	       and tahun='".$tahun."'";
    try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5catu 
	      (kodeorg, tahun, kelompok, keterangan, jumlah)
	      values('".$kodeorg."',".$tahun.",'".$kode."','".$keterangan."',".$jumlah.")";
    try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5catu
	 where kodeorg='".$kodeorg."' and kelompok='".$kode."'
	 and tahun=".$tahun;
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
default:
   break;					
}
	$str1="select *
	     from ".$dbname.".sdm_5catu 
		   where kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'
		  order by tahun desc,kelompok";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	
	while($bar1=$res1->fetch())
	{
		echo"<tr class=rowcontent>
		        <td align=center>".$bar1->kodeorg."</td>
                                        <td align=center>".$bar1->tahun."</td>
                                        <td align=center>".$bar1->kelompok."</td>    
                                         <td>".$bar1->keterangan."</td>    
                                        <td align=right>".$bar1->jumlah."</td>
                                       
                                        <td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kodeorg."','".$bar1->tahun."','".$bar1->kelompok."','".$bar1->keterangan."','".$bar1->jumlah."');\"></td></tr>";
	}				 

?>
