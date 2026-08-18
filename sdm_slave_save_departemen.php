<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kode = checkPostGet('kode','');
$nama = checkPostGet('nama','');
$aktif = checkPostGet('aktif','');
$method = checkPostGet('method','');
$tipe = checkPostGet('tipe','');
$check = checkPostGet('check','');



$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_5departemen set nama='".$nama."',aktif='".$aktif."'
	       where kode='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'insert':
	$str="insert into ".$dbname.".sdm_5departemen (kode,nama,aktif)
	      values('".$kode."','".$nama."','".$aktif."')";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_5departemen 
	where kode='".$kode."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case'loaddata':
	$str1="select * from ".$dbname.".sdm_5departemen order by aktif desc, kode";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch()){
		$dis="";
		if($bar1->aktif=='0'){
			$dis="disabled";
		}
		
		echo"<tr class=rowcontent><td align=center>".$bar1->kode."</td>
		<td>".$bar1->nama."</td>
		<td align=center>" . $arrstatus[$bar1->aktif] . "</td>";
		$ceck=makeOption($dbname,'sdm_5departemen_detail','kode,kode',"kode='".$bar1->kode."' and unittipe='GLOBAL'");
		$no=1;
		if($ceck[$bar1->kode]!=''){
			echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kode."','GLOBAL');></td>";
		}else{				
			echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kode."','GLOBAL');></td>";
		}
		
		$str="select distinct tipe from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by substr(tipe,1,1) asc";
		$res = fetchData($str);
		foreach($res as $bar){
			$ceck=makeOption($dbname,'sdm_5departemen_detail','kode,kode',"kode='".$bar1->kode."' and unittipe='".$bar['tipe']."'");
			$no++;
			if($ceck[$bar1->kode]!=''){
				echo"<td align=center><input ".$dis." type='checkbox' checked onchange=simpandetail(this,'".$bar1->kode."','".$bar['tipe']."');></td>";
			}else{				
				echo"<td align=center><input ".$dis." type='checkbox' onchange=simpandetail(this,'".$bar1->kode."','".$bar['tipe']."'); ></td>";
			}
		}
	echo"<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->nama."','".$bar1->aktif."');\"></td></tr>";
	}

   break;
case'simpandetail':
		if($kode!=''){
			if($check=='1'){
				$str="insert into ".$dbname.".sdm_5departemen_detail (kode,unittipe)
				  values('".$kode."','".$tipe."')";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}else{
				$str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$kode."' and unittipe='".$tipe."'";
				try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			}
		}else{
			$str="select * from ".$dbname.".sdm_5departemen where aktif='1'";
			$res = fetchData($str);
			foreach($res as $bar){
				if($check=='1'){
					$str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$bar['kode']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
					
					$str="insert into ".$dbname.".sdm_5departemen_detail (kode,unittipe)
					  values('".$bar['kode']."','".$tipe."')";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}else{
					$str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$bar['kode']."' and unittipe='".$tipe."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
				}
			}
		}
   
   break;
   
}
?>
