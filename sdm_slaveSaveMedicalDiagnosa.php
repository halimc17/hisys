<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$idx = checkPostGet('idx','');
$name = ucwords(checkPostGet('name',''));
$kodekelompok = checkPostGet('kodekelompok','');

if(trim($idx==''))
{
$str="insert into ".$dbname.".sdm_5diagnosa(kodekelompok,diagnosa)values('".$kodekelompok."','".$name."')";
}
else
{
$str="update ".$dbname.".sdm_5diagnosa
      set diagnosa='".$name."',
	  kodekelompok='".$kodekelompok."'
      where 
	  id=".$idx;
}

try{
	$owlPDO->exec($str); 
	
	$str="select * from ".$dbname.".sdm_5diagnosa order by diagnosa";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch())
	{
		echo"<tr class=rowcontent>
			  <td class=firsttd>".$bar->id."</td>
			  <td>".$bar->kodekelompok."</td>
			  <td>".$bar->diagnosa."</td>
			  <td><img src=images/edit.png align=middle style='cursor:pointer;' onclick=\"editDiagnosa('".$bar->id."','".$bar->kodekelompok."','".$bar->diagnosa."');\" height=17px align=right title='Edit data for ".$bar->diagnosa."'></td>
			 </tr>";
	}
}
catch (PDOException $e){
	echo " Gagal,".addslashes($e->getMessage());
}
?>