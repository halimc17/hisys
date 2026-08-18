<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$name = checkPostGet('name','');
$id = checkPostGet('id','');
$plus = checkPostGet('plus','');
$type = checkPostGet('type','');
$lock = checkPostGet('lock','');
$pph21 = checkPostGet('pph21','');
$irregular = checkPostGet('irregular','');

$arryesno = array('0'=>"TIDAK", '1'=>'YA');
$arryesno2 = array('0'=>"TIDAK", '1'=>'YA', '3'=>'SEMI');


	if(trim($id)!='')
	{
		$str="update ".$dbname.".sdm_ho_component set name='".$name."',
		plus=".$plus.",type='".$type."',`lock`=".$lock.",`pph21`=".$pph21.",`irregular`=".$irregular." where id=".$id;
	}
	else
	{
		$str="insert into ".$dbname.".sdm_ho_component 
		(name,plus,type,`lock`,`pph21`,`irregular`) values('".$name."','".$plus."','".$type."',".$lock.",'".$pph21."','".$irregular."')";	
	}
	try{
		$owlPDO->exec($str); 
		$str="select * from ".$dbname.".sdm_ho_component order by id";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			echo "<tr class=rowcontent style=height:25px><td class=fisttd align=center>".$no."</td>
			      <td>".$bar->name."</td>
			      <td>".($bar->plus==1?$_SESSION['lang']['penambah']:$_SESSION['lang']['pengurang'])."</td>
				  <td>".$bar->type."</td>
				  <td>".($bar->lock==1?$_SESSION['lang']['dikunci']:$_SESSION['lang']['inputbebas'])."</td>
			      <td>".$arryesno[$bar->pph21]."</td>
			      <td>".$arryesno2[$bar->irregular]."</td>
				  <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn title=Edit height=11px onclick=\"editComp('".$bar->id."','".$bar->name."','".$bar->plus."','".$bar->type."','".$bar->lock."','".$bar->pph21."','".$bar->irregular."')\"></td> 
				  <td align=center width=25px><img src=images/application/application_delete.png  height=11px class=zImgBtn title=Delete  onclick=\"delComp('".$bar->id."','".$bar->name."')\"></td>
				  </tr>";
		}	
	}
	catch (PDOException $e){
		echo " Error: ".addslashes($e->getMessage());
		die();
	}
?>
