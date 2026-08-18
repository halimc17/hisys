<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kode   = checkPostGet('kode','');
$nama   = checkPostGet('nama','');
$aktif  = checkPostGet('aktif','');
$method = checkPostGet('method','');
$tipe   = checkPostGet('tipe','');
$check  = checkPostGet('check','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


$arrstatus=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
switch($method){
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
		
		$tab="<div class='table-scroll'>
			<table class=sortable cellspacing=1 border=0 cellpadding=3>
			 <thead>
			 <tr class=rowheader>
			 <th>" . $_SESSION['lang']['nomor'] . "</th>
			 <th>" . $_SESSION['lang']['noakun'] . "</th>
			 <th>" . $_SESSION['lang']['namaakun'] . "</th>
			 ";
			 
			$datadept=array();
			$str = "select * from ".$dbname.".sdm_5departemen_detail a left join ".$dbname.".sdm_5departemen b on a.kode=b.kode where aktif='1' and (unittipe='".$param['tipeorg']."' or unittipe='GLOBAL') order by a.kode";
			$res = fetchData($str);
			foreach($res as $bar){
				$datadept[$bar['kode']]=$bar['nama'];
			}
			$no=0;
			foreach($datadept as $kode => $nama){
				$no++;
				$tab.="<th align=center title=\"".$nama."\">".$kode."<br>
				<input type='checkbox' name=".$kode." id=judul_".$no." onchange=simpandetail(this,'','".$kode."','".$no."');></th>";
			}
			$tab.="</tr>";
			$tab.="</thead>";
			$tab.="<tbody>";
			
			$check=array();
			$str = "select * from ".$dbname.".keu_5akun_detail where tipeorg='".$param['tipeorg']."'";
			$res = fetchData($str);
			foreach($res as $val){
				$check[$val['noakun']][$val['dept']]=$val['dept'];
			}
			
			
			
			$where="";
			if($param['tipeorg']=='KEBUN'){
				$where.=" and noakun like '7%'";
			}
			if($param['tipeorg']=='PABRIK'){
				$where.=" and noakun like '7%'";
			}
			if($param['tipeorg']=='TC'){
				$where.=" and noakun like '82%'";
			}
			if($param['tipeorg']=='RND'){
				$where.=" and noakun like '82%'";
			}
			if($param['tipeorg']=='KANWIL'){
				$where.=" and (noakun like '82%' or noakun like '9%')";
			}
			if($param['tipeorg']=='HOLDING'){
				$where.=" and (noakun like '82%' or noakun like '9%')";
			}
			if($param['tipeorg']=='BULKING'){
				$where.=" and noakun like '81%'";
			}
			$str = "select * from ".$dbname.".keu_5akun where 1=1 ".$where." and aktif='1' and namaakun not like '%NON AKTIF%' order by noakun asc";
			$res = fetchData($str);
			foreach($res as $val){
				$nomor++;
				if(strlen($val['noakun'])<7){
					$style="style=font-weight:bold";
				}else{
					$style="";
				}
				
				$tab.="<tr class=rowcontent ".$style.">";
				$tab.="<td align=center>".$nomor."</td>";
				$tab.="<td name=noakun[] id=noakun_".$nomor.">".$val['noakun']."</td>";
				$tab.="<td nowrap>".$val['namaakun']."</td>";
				
				foreach($datadept as $kode => $nama){
					if(strlen($val['noakun'])==7){						
						if($check[$val['noakun']][$kode]!=''){
							$tab.="<td align=center title=\"".$nama."\" ><input type='checkbox' checked onchange=simpandetail(this,'".$nomor."','".$kode."');></td>";
						}else{				
							$tab.="<td align=center title=\"".$nama."\" ><input type='checkbox' onchange=simpandetail(this,'".$nomor."','".$kode."');></td>";
						}
					}else{
						$tab.="<td align=center title=\"".$nama."\" ></td>";
					}
				}
			}
			
			//echo"<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->nama."','".$bar1->aktif."');\"></td></tr>";
		
		$tab.="</tbody></table></div>";
		
		echo $tab;
	break;
	case'simpandetail':
		
		foreach($param['noakun'] as $key => $noakun){
			if(strlen($noakun)==7){
				if($check=='1'){
					$str="insert into ".$dbname.".keu_5akun_detail (tipeorg,noakun,dept,status)
					  values('".$param['tipeorg']."','".$noakun."','".$param['dept']."','1')";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}else{
					$str="delete from ".$dbname.".keu_5akun_detail where tipeorg='".$param['tipeorg']."' and noakun='".$noakun."' and dept='".$param['dept']."'";
					try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				}
			}
		}
	
		// if($kode!=''){
			// if($check=='1'){
				// $str="insert into ".$dbname.".sdm_5departemen_detail (kode,unittipe)
				  // values('".$kode."','".$tipe."')";
				// try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			// }else{
				// $str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$kode."' and unittipe='".$tipe."'";
				// try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
			// }
		// }else{
			// $str="select * from ".$dbname.".sdm_5departemen";
			// $res = fetchData($str);
			// foreach($res as $bar){
				// if($check=='1'){
					// $str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$bar['kode']."' and unittipe='".$tipe."'";
					// try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
					
					// $str="insert into ".$dbname.".sdm_5departemen_detail (kode,unittipe)
					  // values('".$bar['kode']."','".$tipe."')";
					// try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}			
				// }else{
					// $str="delete from ".$dbname.".sdm_5departemen_detail where kode='".$bar['kode']."' and unittipe='".$tipe."'";
					// try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal,".addslashes($e->getMessage());}	
				// }
			// }
		// }
	break;
   
}
?>
