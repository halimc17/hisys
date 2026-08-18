<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$thnbudget=$_POST['thnbudget'];
$kdpks=$_POST['kdpks'];
$jamo=$_POST['jamo'];
$jamb=$_POST['jamb'];
$arrEnum=getEnum($dbname,'bgt_jam_operasioal_pks','jamolah,breakdown');
$method=$_POST['method'];

		


switch($method)
{
case 'update':	
	$str="update ".$dbname.".bgt_jam_operasioal_pks set jamolah='".$jamo."',breakdown='".$jamb."'
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'";
	try{
		$owlPDO->exec($str);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	break;
case 'insert':
    $str="select * from ".$dbname.".bgt_jam_operasioal_pks 
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'
            limit 0,1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);     
    while($bar=$res->fetch())
    {
        $sudahada="1";
		$pesan=$bar->tahunbudget."-".$bar->millcode."-".$bar->jamolah."-".$bar->breakdown;
    }
    if($sudahada=="1"){
        echo " Gagal, data sudah ada: ".$pesan; exit;
    }

    $str="insert into ".$dbname.".bgt_jam_operasioal_pks (`tahunbudget`,`millcode`,`jamolah`,`breakdown`)
		values ('".$thnbudget."','".$kdpks."','".$jamo."','".$jamb."')";
	try{
		$owlPDO->exec($str);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}	
	break;
case 'delete':
	$str="delete from ".$dbname.".bgt_jam_operasioal_pks 
	       where tahunbudget='".$thnbudget."' and millcode='".$kdpks."'";
	try{
		$owlPDO->exec($str);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".bgt_jam_operasioal_pks order by tahunbudget";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ); 
while($bar1=$res1->fetch())
{
		$no+=1;
		echo"<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td align=right>".$bar1->tahunbudget."</td>
			<td align=center>".$bar1->millcode."</td>
			<td align=right>".$bar1->jamolah."</td>
			<td align=right>".$bar1->breakdown."</td>			
		<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->tahunbudget."','".$bar1->millcode."','".$bar1->jamolah."','".$bar1->breakdown."');\"></td></tr>";
}	 

?>