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
	$str="update ".$dbname.".sdm_5catuporsi set 
	       keterangan='".$keterangan."'
	       where kode='".$kode."' ";
		  // exit("Error:$str");
    try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5catuporsi 
	      (kode, keterangan)
	      values('".$kode."','".$keterangan."')";
    try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5catuporsi
	 where kode='".$kode."'";
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
	     from ".$dbname.".sdm_5catuporsi";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	
	while($bar1=$res1->fetch())
	{
		echo"<tr class=rowcontent>
		        <td align=center>".$bar1->kode."</td>
                                         <td>".$bar1->keterangan."</td>    
                                       
                                        <td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."');\"></td></tr>";
	}				 

?>
