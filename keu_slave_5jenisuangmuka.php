<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$kode=checkPostGet('kode','');
	$namajenis=checkPostGet('namajenis','');
	$noakun=checkPostGet('noakun','');
	$sumber=checkPostGet('sumber','');
		$sts=checkPostGet('sts','');

	$createdBy = $updateBy = $_SESSION['standard']['userid'];

	
	
	$method=checkPostGet('method','');
	
	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'insert':
			if($kode==''||$namajenis==''){
				echo "Gagal : Kode dan nama jenis harus diisi.";
				exit();
			}
			
			$str="select * from ".$dbname.".keu_5jenisuangmuka where kode='".$kode."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if($numRows>=1){
				echo "Error: Kode tagihan sudah pernah terdaftar sebelumnya.";
			}else{
				$str="insert into ".$dbname.".keu_5jenisuangmuka (kode,nama_uangmuka,noakun,source,status,createby) 
				values ('".$kode."','".$namajenis."','".$noakun."','".$sumber."','".$sts."','".$createdBy."')";
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
			if($kode==''||$namajenis==''){
				echo "Gagal : Kode dan nama jenis harus diisi.";
				exit();
			}
			$str="update ".$dbname.".keu_5jenisuangmuka set status='".$sts."',  nama_uangmuka='".$namajenis."',source='".$sumber."',noakun='".$noakun."',updateby='".$updateBy."' where kode='".$kode."'";
			try{
				$owlPDO->exec($str); 
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".keu_5jenisuangmuka where kode='".$kode."'";
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
		$arrSts=array("1"=>"Aktif","0"=>"Non Aktif");
		$str="select * from ".$dbname.".keu_5jenisuangmuka";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		if($numrows<=0){
			echo"<tr class=rowcontent>
				<td colspan=7 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td>
			</tr>";
		}else{
			while($bar=$res->fetch())
			{
				$no+=1;
				echo"<tr class=rowcontent>
						<td style='text-align:right;'>".$no."</td>
						<td>".$bar->kode."</td>
						<td>".$bar->nama_uangmuka."</td>
						<td>".$bar->source."</td>
						<td>".$bar->noakun."</td>
						<td>".$arrSts[$bar->status]."</td>

						
						<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar->kode."','".$bar->nama_uangmuka."','".$bar->source."','".$bar->noakun."','".$bar->status."')\"></td>
						<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$bar->kode."')\"></td>
					</tr>";
			}
		}
	}
?>