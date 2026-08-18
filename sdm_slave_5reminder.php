<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	
	$method=checkPostGet('method','');
	$pt=checkPostGet('pt','');
	$departemen=checkPostGet('departemen','');
	$pic=checkPostGet('pic','');
	$email=checkPostGet('email','');
	
	switch($method)
	{
		case 'getpic':
			$whrdep = "";
			$optpic="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			
			if($departemen!='')
			{
				$whrdep = " and a.bagian='".$departemen."'";
			}
			$str = "select a.karyawanid,a.namakaryawan from ".$dbname.".datakaryawan a
					left join ".$dbname.".organisasi b on a.lokasitugas = b.kodeorganisasi
					left join ".$dbname.".sdm_5departemen c on a.bagian = c.kode
					where b.induk = '".$pt."' ".$whrdep."";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch())
			{
				if($bar['karyawanid']==$pic)
				{
					$optpic.="<option value='".$bar['karyawanid']."' selected>".$bar['namakaryawan']."</option>";
				}
				else
				{
					$optpic.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
				}
			}
			
			echo $optpic;
		break;
		
		case 'getemail':
			$str = "select email from ".$dbname.".datakaryawan where karyawanid = '".$pic."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			
			echo $bar['email'];
		break;
		
		case 'loaddata':
			$str="select * from ".$dbname.".sdm_5reminderemail order by pt asc, departemen asc, email asc";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$no=0;
			while($bar=$res->fetch())
			{
				$no+=1;
				$optpt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['pt']."'");
				$optdep = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar['departemen']."'");
				$optkar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
				echo"<tr class=rowcontent>
						<td style='text-align:right;'>".$no."</td>
						<td>".$bar['pt']." - ".$optpt[$bar['pt']]."</td>
						<td>".$bar['departemen']." - ".$optdep[$bar['departemen']]."</td>
						<td>".$bar['karyawanid']." - ".$optkar[$bar['karyawanid']]."</td>
						<td>".$bar['email']."</td>
						<td>
							<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"edit('".$bar['pt']."','".$bar['departemen']."','".$bar['karyawanid']."','".$bar['email']."')\">
						</td>
						<td>
							<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"hapus('".$bar['karyawanid']."')\">
						</td>
					</tr>";
			}
		break;
		
		case 'insert':
			if($pt==''||$departemen==''||$pic==''||$email=='')
			{
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			
			$str="select * from ".$dbname.".sdm_5reminderemail where karyawanid='".$pic."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if($numrows>=1)
			{
				echo "Error: PIC pernah terdaftar sebelumnya.";
			}
			else
			{
				$str = "insert into ".$dbname.".sdm_5reminderemail (karyawanid,pt,departemen,email) 
				values ('".$pic."','".$pt."','".$departemen."','".$email."')";
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e)
				{
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
			
		case 'edit':
			if($pt==''||$departemen==''||$pic==''||$email=='')
			{
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			
			$str="update ".$dbname.".sdm_5reminderemail set email='".$email."' where karyawanid='".$pic."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'hapus':
			$str="delete from ".$dbname.".sdm_5reminderemail where karyawanid='".$pic."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		default:
        break;	
	}
?>