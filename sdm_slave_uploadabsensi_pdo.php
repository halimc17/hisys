<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	include_once('lib/zLib.php');
	
	$kdOrg=checkPostGet('kdOrg','');
	$tglAbsen=checkPostGet('tglAbsen','');
	$krywnId=checkPostGet('krywnId','');
	$absniId=checkPostGet('absniId','');
	$noba=checkPostGet('noba','');
	$method=checkPostGet('method','');
	
	$updfr=checkPostGet('updfr','');
	
	switch($method){
		case 'loaddata':
			getContainer($kdOrg,tanggalsystem($tglAbsen));
		break;
		
		case 'preview2':
			getContainer2($kdOrg,tanggalsystem($tglAbsen));
		break;
		
		case 'insert':
			if($kdOrg==''){
				echo "Gagal : Kode Organisasi harus dipilih.";
				exit();
			}
			if($tglAbsen==''){
				echo "Gagal : Tanggal harus diisi.";
				exit();
			}
			if($krywnId==''){
				echo "Gagal : Nama Karyawan harus dipilih.";
				exit();
			}
			
			$str="select * from ".$dbname.".upload_absensi where kodeorg='".$kdOrg."' and tanggalabsen='".tanggalsystem($tglAbsen)."' and karyawanid='".$krywnId."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=$qry->rowCount();
			if($numrows>=1){
				echo "Error: Absen sudah pernah diinput sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".upload_absensi (kodeorg,tanggalabsen,karyawanid,absensi,sumber,flag,tanggalinput,userid,noba) 
				values ('".$kdOrg."','".tanggalsystem($tglAbsen)."','".$krywnId."','".$absniId."','manual','0','".date('Y-m-d')."','".$_SESSION['standard']['userid']."','".$noba."')";
				try{
					$owlPDO->exec($strIns); 
					getContainer($kdOrg,tanggalsystem($tglAbsen));
				}catch (PDOException $e){
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
		
		case 'getkaryawan':
			if (strlen($kdOrg) > 4)
			{
				$where = " a.subbagian='".$kdOrg."'  and (a.tanggalkeluar>".tanggalsystem($tglAbsen)." or a.tanggalkeluar='0000-00-00')";
			}
			else
			{
				$where = " a.lokasitugas='".$kdOrg."' and (a.subbagian IS NULL or a.subbagian='0' or a.subbagian='') and (a.tanggalkeluar>".tanggalsystem($tglAbsen)." or a.tanggalkeluar='0000-00-00')";
			}
			$optKry = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$str = "select a.karyawanid,a.namakaryawan,a.nik,a.subbagian,a.tipekaryawan,b.tipe from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5tipekaryawan b on a.tipekaryawan=b.id 
			where ".$where." and a.tipekaryawan not in ('0','7','8') ORDER BY a.namakaryawan ASC";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) 
			{
				$optKry.="<option value='" . $bar['karyawanid'] . "'>" . $bar['namakaryawan'] . "  [" . $bar['nik'] . "]  " . $bar['tipe'] . "</option>";
			}
			echo $optKry;
		break;
			
		// case 'edit':
			// if($kdOrg==''){
				// echo "Gagal : Kode Organisasi harus dipilih.";
				// exit();
			// }
			// if($tglAbsen==''){
				// echo "Gagal : Tanggal harus diisi.";
				// exit();
			// }
			// if($krywnId==''){
				// echo "Gagal : Nama Karyawan harus dipilih.";
				// exit();
			// }
			
			// $str="update ".$dbname.".kebun_5dendapanen set jenisdenda='".$jenisdenda."', denda='".$nilaidenda."', deskripsi='".$ketdenda."' where kodeorg='".$kd_org."' and kodedenda='".$kd_denda."'";
			// try{
				// $owlPDO->exec($str); 
				// getContainer();
			// }catch (PDOException $e){
				// echo "DB Error : ".$e->getMessage();
				// die();
			// }
		// break;
		
		case 'hapus':
			$str="delete from ".$dbname.".upload_absensi where kodeorg='".$kdOrg."' and tanggalabsen='".tanggalsystem($tglAbsen)."' and karyawanid='".$krywnId."'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'uploaddata':
			$str = "select * from ".$dbname.".datakaryawan where nik = '".$updfr."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nik = $bar['nik'];
			$karyawanid = $bar['karyawanid'];
			$loctugas = ($bar['subbagian']=='' ? $bar['lokasitugas'] : $bar['subbagian']);
			
			$str="delete from ".$dbname.".upload_absensi where tanggalabsen='".tanggalsystem($tglAbsen)."' and karyawanid='".$karyawanid."'";
			
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
				
			$str="insert into ".$dbname.".upload_absensi (kodeorg,tanggalabsen,karyawanid,absensi,sumber,flag,tanggalinput,userid) values ('".$loctugas."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','finger','0','".date('Y-m-d')."','".$_SESSION['standard']['userid']."'
			)";
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
			
			$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($loctugas,0,4)."'");
			if($optOrg[$loctugas]=="PABRIK")
			{
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='1'");
				$gajipokok = @($optGaji[$karyawanid]/25);
				
				$str = "select count(tanggal) as counttanggal from ".$dbname.".sdm_absensiht where tanggal='".tanggalsystem($tglAbsen)."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				$counttanggal = $bar['counttanggal'];
				if($counttanggal==0)
				{
					$str="insert into ".$dbname.".sdm_absensiht (tanggal,kodeorg,periode,posting) values ('".tanggalsystem($tglAbsen)."','".$loctugas."','".substr(tanggalsystem($tglAbsen),0,4)."-".substr(tanggalsystem($tglAbsen),4,2)."','0')";
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
				
				$str="delete from ".$dbname.".sdm_absensidt where karyawanid='".$karyawanid."' and tanggal='".tanggalsystem($tglAbsen)."'";
				try
				{
					$owlPDO->exec($str); 
				}
				catch (PDOException $e)
				{
					echo "DB Error : ".$e->getMessage();
					die();
				}
				
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,absensi,catu,fingerprint,hk,umr) values ('".$loctugas."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','1','1','1','".$gajipokok."')";
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
		
		default:
        break;	
	}
	
	function getContainer($kdOrg,$tanggal){
		global $owlPDO;
		global $dbname;
		
		$str="select a.*,b.namakaryawan,c.keterangan,d.namaorganisasi from ".$dbname.".upload_absensi a 
			left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
			left join ".$dbname.".sdm_5absensi c on a.absensi=c.kodeabsen
			left join ".$dbname.".organisasi d on a.kodeorg=d.kodeorganisasi
			where kodeorg='".$kdOrg."' and tanggalabsen='".$tanggal."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($res=$qry->fetch())
		{
			$no+=1;
			echo"<tr class=rowcontent>
					<td style='text-align:right;'>".$no."</td>
					<td>".$res->kodeorg." - ".$res->namaorganisasi."</td>
					<td>".$res->noba."</td>
					<td>".tanggalnormal($res->tanggalabsen)."</td>
					<td>".$res->namakaryawan."</td>
					<td style='text-align:center;'>".$res->keterangan."</td>";
			if($res->flag==0){
				echo "<td><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"hapus('".$res->kodeorg."','".tanggalnormal($res->tanggalabsen)."','".$res->karyawanid."')\"></td>";
			}
			else
			{
				echo "<td></td>";
			}
			echo "</tr>";
		}
	}
	
	function getContainer2($kdOrg,$tanggal){
		global $owlPDO;
		global $dbname;
		
		if($kdOrg==''){
			exit("warning : Kode organisasi harus dipilih.");
		}
		
		$str="select * from ".$dbname.".sdm_5ipfinger where kodeorg='".$kdOrg."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();
		if($numrows<=0){
			exit("warning: Silahkan setup ip fingerprint di menu sdm->setup->ip finger.");
		}else{
			$bar=$res->fetch();
			$ipAdd=$bar['ip'];
			$dbnm=$bar['dbname'];
			$tblnm=$bar['tblname'];
			$usrName=$bar['username'];
			$pswrd=$bar['password'];
			$prt=$bar['port'];
			
			try 
			{
				$owlPDO = new PDO('mysql:host='.$ipAdd.':'.$prt, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
				$owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}
			catch (PDOException $e) 
			{
				print " Gagal, could not connect\n";	
				print "Error!: " . $e->getMessage() . "<br>";
				die();
			}
			
			$sColom="SHOW COLUMNS FROM ".$dbnm.".".$tblnm."";
			
			$i = 0;
			$tColom=array();
			$tmpCol ="";
			
			$qColom=$owlPDO->query($sColom) or die(print " Gagal: ".PDOException::getMessage());
			$qColom->setFetchMode(PDO::FETCH_ASSOC);
			while($rColom=$qColom->fetch())
			{
				$tColom[$i]=$rColom['Field'];
				$i++;
			}
			
			$a=0;
			foreach($tColom as $dt =>$isi)
			{
				if($tmpCol=="") 
				{
					$tmpCol.=$isi;
				}
				else 
				{
					$tmpCol.=",".$isi;
				}
			}
			
			$tanggal = substr($tanggal,0,4)."-".substr($tanggal,4,2)."-".substr($tanggal,6,2);
			$sCob="select a.pin,a.scan_date,b.first_name,b.last_name
			from ".$dbnm.".".$tblnm." a
			left join ".$dbnm.".emp b on a.pin = b.nik
			where a.scan_date like '".$tanggal."%' group by a.pin order by b.first_name asc, b.last_name asc";
			$res=$owlPDO->query($sCob) or die(print " Gagal: ".PDOException::getMessage());
			$row=owlBaris($res);
			
			if($row>0)
			{
				echo"<button class=mybutton id='btnupload2' onclick=uploaddata('".$row."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";
				
				echo"<table class=sortable cellspacing=1 border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>No.</td>
					<td style='text-align:center'>".$_SESSION['lang']['nik']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tanggal']."</td>
				</tr>
				</thead>
				<tbody>";
				
				$res->setFetchMode(PDO::FETCH_NUM);
				while($hsl=$res->fetch())
				{
					$no+=1;
					echo"<tr class=rowcontent id='trabsen_".$no."'>
						<td style='text-align:right'>".$no."</td>
						<td id='tdabsen_".$no."'>".$hsl[0]."</td>
						<td>".$hsl[2]." ".$hsl[3]."</td>
						<td>".tanggalnormal(substr($hsl[1],0,10))."</td>
					</tr>";
				}
			}
			else
			{
				echo"<table class=sortable cellspacing=1 border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>No.</td>
					<td style='text-align:center'>".$_SESSION['lang']['karyawanid']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['namakaryawan']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tanggal']."</td>
				</tr>
				</thead>
				<tbody>";
				
				echo"<tr class=rowcontent align=center>
					<td colspan=4 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
				</tr>";
			}
			echo"</tbody></table></div>";
		}
	}
?>