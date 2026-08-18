<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$no          = checkPostGet('no','');
$kode        = checkPostGet('kode','');
$nama        = checkPostGet('nama','');
$aktif       = checkPostGet('aktif','');
$method      = checkPostGet('method','');
$kodegolongan= checkPostGet('kodegolongan','');
$tipe        = checkPostGet('tipe','');
$check       = checkPostGet('check','');

$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);

switch ($method) {
	case'simpandetail':
		if($kodegolongan!=''){
			if($check=='1'){
				$str="insert into ".$dbname.".sdm_5tipekaryawan_detail (id,unittipe)
				  values('".$kodegolongan."','".$tipe."')";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}else{
				$str="delete from ".$dbname.".sdm_5tipekaryawan_detail where id='".$kodegolongan."' and unittipe='".$tipe."'";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}
		}else{
			$str = "select * from ".$dbname.".sdm_5tipekaryawan";
			$res = fetchData($str);
			foreach($res as $bar){
				if($check=='1'){
					$str="delete from ".$dbname.".sdm_5tipekaryawan_detail where id='".$bar['id']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
					
					$str="insert into ".$dbname.".sdm_5tipekaryawan_detail (id,unittipe)
					values('".$bar['id']."','".$tipe."')";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}else{
					$str="delete from ".$dbname.".sdm_5tipekaryawan_detail where id='".$bar['id']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
				}
			}
		}
   
	break;
    case 'update':
        $str = "update " . $dbname . ".sdm_5tipekaryawan set tipe='" . $nama . "', no='".$no."', aktif='".$aktif."'
	       where id='" . $kode . "'";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
        break;
    case 'insert':
	
		#= ambil kode terakhir
	
		$str = "select max(id) as id from " . $dbname . ".sdm_5tipekaryawan";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar = $res->fetch();
			$nomax=$bar->id;
			$kode=$nomax+1;
			
		
        $str = "insert into " . $dbname . ".sdm_5tipekaryawan (no,id,tipe,aktif)
	      values('".$no."','" . $kode . "','" . $nama . "','" . $aktif . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
        break;
    case 'delete':
        $str = "delete from " . $dbname . ".sdm_5tipekaryawan 
	where id='" . $kode . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
        break;
    case'loaddata':
		$str1 = "select * from " . $dbname . ".sdm_5tipekaryawan order by aktif desc, no asc";
		$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {

			$dis="";
			if($bar1->aktif=='0'){
				$dis="disabled";
			}

			echo"<tr class=rowcontent>
				<td align=center>" . $bar1->no . "</td>
				<td align=center hidden>" . $bar1->id . "</td>
				<td>" . $bar1->tipe . "</td>
				<td align=center>" . $arrstatus[$bar1->aktif] . "</td>";
				
				$ceck=makeOption($dbname,'sdm_5tipekaryawan_detail','id,unittipe',"id='".$bar1->id."' and unittipe='GLOBAL'");
				$no=1;
				if($ceck[$bar1->id]!=''){
					echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->id."','GLOBAL');></td>";
				}else{				
					echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->id."','GLOBAL');></td>";
				}
				
				$str = "select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
				$res = fetchData($str);
				foreach($res as $bar){
					$ceck = makeOption($dbname,'sdm_5tipekaryawan_detail','id,unittipe',"id='".$bar1->id."' and unittipe='".$bar['tipe']."'");
					$no++;
					if($ceck[$bar1->id]!=''){
						echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->id."','".$bar['tipe']."');></td>";
					}else{				
						echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->id."','".$bar['tipe']."'); ></td>";
					}
				}
				
			echo"<td  align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->no . "','" . $bar1->id . "','" . $bar1->tipe . "','" . $bar1->aktif . "');\"></td>
				</tr>";
		}
	
	break;
}
?>