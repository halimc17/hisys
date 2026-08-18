<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodejabatan = checkPostGet('kodejabatan','');
$namajabatan = checkPostGet('namajabatan','');
$aktif = checkPostGet('aktif', '');
$method = checkPostGet('method','');
$tipe = checkPostGet('tipe','');
$check = checkPostGet('check','');

$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
switch($method){
case 'update':	
	$str="update ".$dbname.".sdm_5jabatan set namajabatan='".$namajabatan."',aktif='".$aktif."'
	       where kodejabatan='".$kodejabatan."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5jabatan (kodejabatan,namajabatan,aktif)
	      values('".$kodejabatan."','".$namajabatan."','" . $aktif . "')";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	
	$str="insert into ".$dbname.".sdm_5jabatan_detail (kodejabatan,unittipe)
	  values('".$kodejabatan."','GLOBAL')";
	try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
	
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5jabatan
	where kodejabatan='".$kodejabatan."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
	}
	break;
case'loaddata':
	$str1="select * from ".$dbname.".sdm_5jabatan order by kodejabatan";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch()){

		$dis="";
		if($bar1->aktif=='0'){
			$dis="disabled";
		}

		echo"<tr class=rowcontent>
				<td align=center>".$bar1->kodejabatan."</td>
				<td>".$bar1->namajabatan."</td>
				<td align=center>" . $arrstatus[$bar1->aktif] . "</td>";
				
				$ceck=makeOption($dbname,'sdm_5jabatan_detail','kodejabatan,kodejabatan',"kodejabatan='".$bar1->kodejabatan."' and unittipe='GLOBAL'");
				$no=1;
				if($ceck[$bar1->kodejabatan]!=''){
					echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kodejabatan."','GLOBAL'); id=jab_".$bar1->kodejabatan."_".$no."></td>";
				}else{				
					echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kodejabatan."','GLOBAL'); id=jab_".$bar1->kodejabatan."_".$no."></td>";
				}
				
				$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
				$res = fetchData($str);
				foreach($res as $bar){
					$ceck=makeOption($dbname,'sdm_5jabatan_detail','kodejabatan,kodejabatan',"kodejabatan='".$bar1->kodejabatan."' and unittipe='".$bar['tipe']."'");
					$no++;
					if($ceck[$bar1->kodejabatan]!=''){
						echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kodejabatan."','".$bar['tipe']."'); id=jab_".$bar1->kodejabatan."_".$no."></td>";
					}else{				
						echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kodejabatan."','".$bar['tipe']."'); id=jab_".$bar1->kodejabatan."_".$no."></td>";
					}
		}
		echo"<td align=center>
				<img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kodejabatan."','".$bar1->namajabatan."','" . $bar1->aktif . "');\">
			</td>";
		echo"</tr>";
	}	 
	
   break;
   case'simpandetail':
		if($kodejabatan!=''){
			if($check=='1'){
				$str="insert into ".$dbname.".sdm_5jabatan_detail (kodejabatan,unittipe)
				  values('".$kodejabatan."','".$tipe."')";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}else{
				$str="delete from ".$dbname.".sdm_5jabatan_detail where kodejabatan='".$kodejabatan."' and unittipe='".$tipe."'";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}
		}else{
			$str="select * from ".$dbname.".sdm_5jabatan";
			$res = fetchData($str);
			foreach($res as $bar){
				if($check=='1'){
					$str="delete from ".$dbname.".sdm_5jabatan_detail where kodejabatan='".$bar['kodejabatan']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
					
					$str="insert into ".$dbname.".sdm_5jabatan_detail (kodejabatan,unittipe)
					  values('".$bar['kodejabatan']."','".$tipe."')";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}else{
					$str="delete from ".$dbname.".sdm_5jabatan_detail where kodejabatan='".$bar['kodejabatan']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
				}
			}
		}
   
   break;
}
?>
