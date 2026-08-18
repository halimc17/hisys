<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$idsuku=checkPostGet('idsuku','');
	$namasuku=checkPostGet('namasuku','');
	$aktif=checkPostGet('aktif','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($namasuku==''){
				echo "Gagal : Nama suku harus diisi.";
				exit();
			}
			
			$str="select * from ".$dbname.".sdm_5suku where LOWER(namasuku)='".strtolower($namasuku)."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if($numRows>=1){
				echo "Error: Nama suku sudah pernah terdaftar sebelumnya.";
			}else{
				$str="insert into ".$dbname.".sdm_5suku (namasuku,status) 
				values ('".$namasuku."','".$aktif."')";
				try{
					$owlPDO->exec($str); 
					getContainer();
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
			
		case 'edit':
			if($namasuku==''){
				echo "Gagal : Nama suku harus diisi.";
				exit();
			}
			$str="update ".$dbname.".sdm_5suku set namasuku='".$namasuku."',status = '".$aktif."' where idsuku='".$idsuku."'";
			try{
				$owlPDO->exec($str); 
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".sdm_5suku where idsuku='".$idsuku."'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer(){
		global $owlPDO;
		global $dbname;

		$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);

		
		$str="select * from ".$dbname.".sdm_5suku order by idsuku";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		if($numrows<=0){
			echo"<tr class=rowcontent>
				<td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>";
		}else{
			while($bar=$res->fetch())
			{
				echo"<tr class=rowcontent>
						<td align = center>".$bar->idsuku."</td>
						<td>".$bar->namasuku."</td>
						<td align = center>".$arrstatus[$bar->status]."</td>
						<td align = center><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->idsuku."','".$bar->namasuku."','".$bar->status."')\"></td>
					</tr>";
			}
		}
	}
?>