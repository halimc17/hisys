<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodegolongan = checkPostGet('kodegolongan', '');
$namagolongan = checkPostGet('namagolongan', '');
$aktif = checkPostGet('aktif', '');
$method = checkPostGet('method', '');
$tipe = checkPostGet('tipe','');
$check = checkPostGet('check','');


$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);

switch ($method) {
    case 'update':
        $str = "update " . $dbname . ".sdm_5golongan set namagolongan='" . $namagolongan . "',aktif='".$aktif."'
	       where kodegolongan='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
    break;
    case 'insert':
        $str = "insert into " . $dbname . ".sdm_5golongan (kodegolongan,namagolongan,aktif)
	      values('" . $kodegolongan . "','" . $namagolongan . "','" . $aktif . "')";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
		
		$str="insert into ".$dbname.".sdm_5golongan_detail (kodegolongan,unittipe)
				  values('".$kodegolongan."','GLOBAL')";
		try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				
    break;
    case 'delete':

        $str = "delete from " . $dbname . ".sdm_5golongan where kodegolongan='" . $kodegolongan . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
    break;
    case'loaddata':
		$str1 = "select * from " . $dbname . ".sdm_5golongan order by namagolongan asc";
		$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_OBJ);
		while ($bar1 = $res1->fetch()) {

			$dis="";
			if($bar1->aktif=='0'){
				$dis="disabled";
			}

			echo"<tr class=rowcontent>
					<td align=center>" . $bar1->kodegolongan . "</td>
					<td>" . $bar1->namagolongan . "</td>
					<td align=center>" . $arrstatus[$bar1->aktif] . "</td>";
					
					$ceck=makeOption($dbname,'sdm_5golongan_detail','kodegolongan,kodegolongan',"kodegolongan='".$bar1->kodegolongan."' and unittipe='GLOBAL'");
					$no=1;
					if($ceck[$bar1->kodegolongan]!=''){
						echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kodegolongan."','GLOBAL');></td>";
					}else{				
						echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kodegolongan."','GLOBAL');></td>";
					}
					
					$str = "select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
					$res = fetchData($str);
					foreach($res as $bar){
						$ceck=makeOption($dbname,'sdm_5golongan_detail','kodegolongan,kodegolongan',"kodegolongan='".$bar1->kodegolongan."' and unittipe='".$bar['tipe']."'");
						$no++;
						if($ceck[$bar1->kodegolongan]!=''){
							echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kodegolongan."','".$bar['tipe']."');></td>";
						}else{				
							echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kodegolongan."','".$bar['tipe']."'); ></td>";
						}
					}	
			echo"<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->kodegolongan . "','" . $bar1->namagolongan . "','" . $bar1->aktif . "');\"></td></tr>";
		}
	
    break;
	case'simpandetail':
		if($kodegolongan!=''){
			if($check=='1'){
				$str="insert into ".$dbname.".sdm_5golongan_detail (kodegolongan,unittipe)
				  values('".$kodegolongan."','".$tipe."')";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}else{
				$str="delete from ".$dbname.".sdm_5golongan_detail where kodegolongan='".$kodegolongan."' and unittipe='".$tipe."'";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}
		}else{
			$str="select * from ".$dbname.".sdm_5golongan";
			$res = fetchData($str);
			foreach($res as $bar){
				if($check=='1'){
					$str="delete from ".$dbname.".sdm_5golongan_detail where kodegolongan='".$bar['kodegolongan']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
					
					$str="insert into ".$dbname.".sdm_5golongan_detail (kodegolongan,unittipe)
					  values('".$bar['kodegolongan']."','".$tipe."')";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}else{
					$str="delete from ".$dbname.".sdm_5golongan_detail where kodegolongan='".$bar['kodegolongan']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
				}
			}
		}
   
   break;
   
}
?>
