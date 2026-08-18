<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kode=checkPostGet('kode','');
	$deskripsi=checkPostGet('deskripsi','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loadData':
			getContainer();
		break;
		
		case 'insert':
			if($kode==''||$deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".kebun_5kodedendapanen where kodedenda='".$kode."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numRows=owlBaris($qry);
			if($numRows>=1){
				echo "Error: Kode denda sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5kodedendapanen (kodedenda,deskripsi) 
				values ('".$kode."','".$deskripsi."')";
				try{
					$owlPDO->exec($strIns); 
					getContainer();
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
			
		case 'edit':
			if($deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			
			$str="update ".$dbname.".kebun_5kodedendapanen set deskripsi='".$deskripsi."' where kodedenda='".$kode."'";
			try{
				$owlPDO->exec($str); 
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".kebun_5kodedendapanen where kodedenda='".$kode."'";
			try{
				$owlPDO->exec($str); 
				getContainer();
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
		
		$str="select * from ".$dbname.".kebun_5kodedendapanen order by kodedenda ASC";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($res=$qry->fetch())
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodedenda."</td>
					<td>".$res->deskripsi."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kodedenda."','".$res->deskripsi."')\"></td>
					<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$res->kodedenda."')\"></td>
				</tr>";
		}
	}
?>