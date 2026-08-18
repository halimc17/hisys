<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$kode = checkPostGet('kode','');
$keterangan = checkPostGet('keterangan','');
$method = checkPostGet('method','');

switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_5jenissp set keterangan='".$keterangan."'
	       where kode='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5jenissp (kode,keterangan)
	      values('".$kode."','".$keterangan."')";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5jenissp
	where kodejabatan='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".sdm_5jenissp order by kode";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%;'>
     <thead>
	 <tr align=center class=rowheader>
	 	<td style='width:150px;'>".$_SESSION['lang']['tipe']."</td>
		<td>".$_SESSION['lang']['keterangan']."</td>
		<td  style='width:30px;'>*</td></tr>
	 </thead>
	 <tbody>";
		while($bar1=$res1->fetch())
		{
			echo"<tr class=rowcontent>
				<td align=center>".$bar1->kode."</td>
				<td>".$bar1->keterangan."</td>
				<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."');\"></td>
			</tr>";
		}	 
echo"	 
	 </tbody>
	 <tfoot>
	 </tfoot>
	 </table>";

?>
