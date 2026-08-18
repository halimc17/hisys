<?
require_once('master_validation.php');
require_once('config/connection.php');

$jenisvhc=$_POST['jenisvhc'];
$namajenisvhc=$_POST['namajenisvhc'];
$noakun=$_POST['noakun'];
$method=$_POST['method'];

switch($method)
{
case 'update':	
	$str="update ".$dbname.".vhc_5jenisvhc set namajenisvhc='".$namajenisvhc."'
	      ,noakun='".$noakun."' where jenisvhc='".$jenisvhc."'";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}
break;
case 'insert':
	$str="insert into ".$dbname.".vhc_5jenisvhc(jenisvhc,namajenisvhc,noakun)
	      values('".$jenisvhc."','".$namajenisvhc."','".$noakun."')";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}	
break;
case 'delete':
	$str="delete from ".$dbname.".vhc_5jenisvhc 
	where jenisvhc='".$jenisvhc."'";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}
break;
default:
   break;					
}
$str1="select * from ".$dbname.".vhc_5jenisvhc order by jenisvhc";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
if(owlBaris($res1)!=0)
{
	while($bar1=$res1->fetch())
	{
			echo"<tr class=rowcontent><td align=center>".$bar1->jenisvhc."</td>
			     <td>".$bar1->namajenisvhc."</td>
				 <td>".$bar1->noakun."</td>
				 <td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->jenisvhc."','".$bar1->namajenisvhc."','".$bar1->noakun."');\"></td></tr>";
	}	 
}
?>
