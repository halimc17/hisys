<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
	
	$id = checkPostGet('id','');
	$arAkun2 = makeOption($dbname,"keu_5akun", "noakun,namaakun","NOT (substr(noakun, 1,1)=2 or substr(noakun, 1,1)=6 or substr(noakun, 1,1)=7 or substr(noakun, 1,1)=8)");
	
	$str="delete from ".$dbname.".sdm_ho_component where id=".$id;	
	try{
		$owlPDO->exec($str); 
		$str="select * from ".$dbname.".sdm_ho_component order by id";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			echo "<tr class=rowcontent><td class=fisttd>".$no."</td>
			      <td>".$bar->name."</td>
			      <td>".($bar->plus==1?$_SESSION['lang']['penambah']:$_SESSION['lang']['pengurang'])."</td>
				  <td>".$bar->type."</td>
				  <td>".($bar->lock==1?$_SESSION['lang']['dikunci']:$_SESSION['lang']['inputbebas'])."</td>
				  <td>".$arAkun2[$bar->noakun]."</td>
				  <td align=center><img src=images/tool.png class=dellicon title=Edit height=11px onclick=\"editComp('".$bar->id."','".$bar->name."','".$bar->plus."','".$bar->type."','".$bar->lock."')\"> 
				  <img src=images/close.png  height=11px class=dellicon title=Delete  onclick=\"delComp('".$bar->id."','".$bar->name."')\"></td>
				  </tr>";
		}	
	}
	catch (PDOException $e){
		echo " Error: ".addslashes($e->getMessage());
		die();
	}
?>
