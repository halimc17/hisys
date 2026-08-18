<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kd_alasan=checkPostGet('kd_alasan','');
	$deskripsi=checkPostGet('deskripsi','');
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($kd_alasan==''||$deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="select * from ".$dbname.".kebun_5alasanrencanasisip where kodealasanrencanasisip='".$kd_alasan."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($qry);
			if($numRows>=1){
				echo "Error: Kode alasan rencana sisip sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5alasanrencanasisip (kodealasanrencanasisip,deskripsi) 
				values ('".$kd_alasan."','".$deskripsi."')";
				// echo $strIns;
				try{
					$owlPDO->exec($strIns);
					getContainer();
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
				}
			}
		break;
			
		case 'edit':
			if($deskripsi==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			$str="update ".$dbname.".kebun_5alasanrencanasisip set deskripsi='".$deskripsi."' where kodealasanrencanasisip='".$kd_alasan."'";
			try{
				$owlPDO->exec($str);
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".kebun_5alasanrencanasisip where kodealasanrencanasisip='".$kd_alasan."'";
			try{
				$owlPDO->exec($str);
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer(){
		global $conn;
		global $dbname;
		global $owlPDO;
		
		$str="select * from ".$dbname.".kebun_5alasanrencanasisip";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		
		while($res=$qry->fetch())
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodealasanrencanasisip."</td>
					<td>".$res->deskripsi."</td>
					<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$res->kodealasanrencanasisip."','".$res->deskripsi."')\"></td>
					<td><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$res->kodealasanrencanasisip."')\"></td>
				</tr>";
		}
	}
?>