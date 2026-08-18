<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$edulevel=checkPostGet('edulevel','');
$eduname=checkPostGet('eduname','');
$edugroup=checkPostGet('edugroup','');
$eduid=checkPostGet('eduid','');
$method=checkPostGet('method','');

switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_5pendidikan set pendidikan='".$eduname."',
	      levelpendidikan=".$edulevel.",kelompok='".$edugroup."' 
	       where idpendidikan=".$eduid;
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5pendidikan (levelpendidikan,pendidikan,kelompok)
	      values(".$edulevel.",'".$eduname."','".$edugroup."')";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}	
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5pendidikan
	where idpendidikan=".$eduid;
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

$str1="select * from ".$dbname.".sdm_5pendidikan order by levelpendidikan";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
	echo"<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%;'>
	     <thead>
		 <tr  align=center class=rowheader>
			<td style='width:150px;'>".$_SESSION['lang']['edulevel']."</td><td>".$_SESSION['lang']['eduname']."</td>
			<td>".$_SESSION['lang']['edugroup']."</td>
			<td style='width:70px;'>*</td>
		 </tr>
		 </thead>
		 <tbody>";
	while($bar1=$res1->fetch())
	{
		echo"<tr class=rowcontent>
		<td align=center>".$bar1->levelpendidikan."</td><td>".$bar1->pendidikan."</td>
		<td>".$bar1->kelompok."</td>
		<td align=center>
			<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar1->levelpendidikan."','".$bar1->pendidikan."','".$bar1->kelompok."',".$bar1->idpendidikan.");\"> 
			<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delPendidikan(".$bar1->idpendidikan.");\">
		</td>
		</tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
?>
