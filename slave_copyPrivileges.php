<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$dari=$_POST['dari'];
$pengguna=$_POST['pengguna'];
$jabatan = checkPostGet('jabatan', '');
$typecopy = checkPostGet('typecopy', '');

switch ($proses) {
	case'user':
		if($typecopy=='newuser'){
			$str="delete from ".$dbname.".auth where namauser='".$pengguna."'";
			$owlPDO->exec($str);

			$str=$owlPDO->query("select menuid, status, lastuser, detail from ".$dbname.".auth where namauser='".$dari."'");
			$str->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$str->fetch())
			{
			$str1="insert into ".$dbname.".auth(namauser,menuid, status, lastuser, detail)
				   values('".$pengguna."',".$bar->menuid.",".$bar->status.",'".$_SESSION['standard']['username']."','".$bar->detail."');";
			  try{
						$owlPDO->exec($str1);
				}
				catch (PDOException $ex) {
						   print " Gagal  !: " . $ex->getMessage() . "<br/>";
							die();
					}
			}
		
		}elseif($typecopy=='allmenu'){
			$str="delete from ".$dbname.".auth where namauser='".$pengguna."'";
			$owlPDO->exec($str);
			
			$str=$owlPDO->query("select id from ".$dbname.".menu");
			$str->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$str->fetch()){
				$str1="insert into ".$dbname.".auth(namauser,menuid, status, lastuser, detail)
				   values('".$pengguna."',".$bar->id.",'1','".$_SESSION['standard']['username']."','0');";
				try{$owlPDO->exec($str1);}catch (PDOException $ex) {print " Gagal  !: " . $ex->getMessage() . "<br/>";die();}
			}
			
		}elseif($typecopy=='alltitle'){
			try {
			$owlPDO->beginTransaction();
		
			$wh="";
			if($pengguna!=''){
				$wh=" and namauser='".$pengguna."'";
			}
			
			$str = "select * from " . $dbname . ".menu where class='devider' or class='title'";
			$res=fetchData($str);
			foreach($res as $bar){				
				$str="delete from ".$dbname.".auth where 1=1 ".$wh." and menuid='".$bar['id']."'";
				$owlPDO->exec($str);
				
				if($pengguna==''){					
					$s = "select * from " . $dbname . ".user";
					$r=fetchData($s);
					foreach($r as $b){						
						$str="insert into ".$dbname.".auth(namauser,menuid, status, lastuser, detail)
						      values('".$b['namauser']."',".$bar['id'].",'1','".$_SESSION['standard']['username']."','0');";
						$owlPDO->exec($str);
					}
				}else{
					$str="insert into ".$dbname.".auth(namauser,menuid, status, lastuser, detail)
						  values('".$pengguna."',".$bar['id'].",'1','".$_SESSION['standard']['username']."','0');";
					$owlPDO->exec($str);
				}
			}
			#execute
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}
		}else{
			$arrUser = array();
			$str = "select b.namauser from ".$dbname.".datakaryawan a 
					left join ".$dbname.".user b on a.karyawanid=b.karyawanid
					where a.kodejabatan='".$jabatan."' and b.namauser != '".$dari."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				$arrUser[] = $bar['namauser'];
			}
			if(count($arrUser)>0)
			{
				foreach($arrUser as $key)
				{
					$str="delete from ".$dbname.".auth where namauser='".$key."'";
					$owlPDO->exec($str);

					$str=$owlPDO->query("select menuid, status, lastuser, detail from ".$dbname.".auth where namauser='".$dari."'");
					$str->setFetchMode(PDO::FETCH_OBJ);
					while($bar=$str->fetch())
					{
						$str1="insert into ".$dbname.".auth(namauser,menuid, status, lastuser, detail)
						values('".$key."',".$bar->menuid.",".$bar->status.",'".$_SESSION['standard']['username']."','".$bar->detail."');";
						
						try
						{
							$owlPDO->exec($str1);
						}
						catch (PDOException $ex) 
						{
							print " Gagal  !: " . $ex->getMessage() . "<br/>";
							die();
						}
					}
				}
			}
		}

	break;
	
	case'getjabatan':
		$jabatan="";
		$str = "select a.namauser, b.kodejabatan, c.namajabatan from ".$dbname.".user a
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
		left join ".$dbname.".sdm_5jabatan c on b.kodejabatan=c.kodejabatan
		where a.namauser='".$dari."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$jabatan = $bar['namajabatan'];
			$kodejabatan = $bar['kodejabatan'];
		}
		echo $kodejabatan."##".$jabatan;
		break;
	

}

?>