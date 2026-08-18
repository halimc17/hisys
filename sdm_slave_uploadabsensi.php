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
	$tgl=tanggalsystemn($tglAbsen);
	
	$updfr=checkPostGet('updfr','');
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
	
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
			
			$karyawanid=$krywnId;
			$lokasitugas=substr($kdOrg,0,4);
			$subbagian=$kdOrg; 
			if($optOrg[$lokasitugas]=="PABRIK")
			{
				
				$insentif=$insentifharikerja=$insentifharilibur=0;
			
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='1' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$gajipokok = @($optGaji[$karyawanid]/25);
				
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='63' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$insentifharikerja =$optGaji[$karyawanid];
				
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='64' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$insentifharilibur =$optGaji[$karyawanid];
				
				
				$str = "select count(tanggal) as counttanggal from ".$dbname.".sdm_absensiht where tanggal='".tanggalsystemn($tglAbsen)."'
						and kodeorg='".$subbagian."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				$counttanggal = $bar['counttanggal'];
				if($counttanggal==0)
				{
					$str="insert into ".$dbname.".sdm_absensiht (tanggal,kodeorg,periode,posting) values ('".tanggalsystem($tglAbsen)."','".$subbagian."','".substr(tanggalsystem($tglAbsen),0,4)."-".substr(tanggalsystem($tglAbsen),4,2)."','0')";
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
				
				
				
				$day = date('D', strtotime($tgl));
				if($day=='Sun')$libur=true; else $libur=false;
				// kamus hari libur
				$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."'  and (kebun='GLOBAL' or kebun='".$lokasitugas."')";
				$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
				$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
				while($roworg=$queorg->fetch()){
					if($roworg['keterangan']=='libur')$libur=true;
					if($roworg['keterangan']=='masuk')$libur=false;
				}
				if($libur==true){
					$hk=0;
					$gajipokok=0;
					$insentifharikerja=0;
				}else{
					$hk=1;
					$gajipokok=$gajipokok;
					$insentifharilibur=0;
				}
				
				
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,absensi,catu,fingerprint,hk,umr,insentif,insentiflibur) 
					values ('".$subbagian."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','1','1','1','".$gajipokok."','".$insentifharikerja."','".$insentifharilibur."')";
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
		
			
			
			
			//$a=substr(tanggalsystem($tglAbsen),0,4);
		
			$str = "select * from ".$dbname.".datakaryawan where nik = '".$updfr."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$nik = $bar['nik'];
			$karyawanid = $bar['karyawanid'];
			$subbagian=$bar['subbagian'];
			$lokasitugas=$bar['lokasitugas'];
			if($subbagian==''){
				$subbagian=$lokasitugas;
			}
			
			/*
			#cek apakah sudah ada periode penggajian unit
			$strv=" select count(*) as jumlah from ".$dbname.".sdm_5periodegaji where kodeorg='".$bar['lokasitugas']."'
					and periode='".substr(tanggalsystem($tglAbsen),0,7)."' and sudahproses=0 and jenisgaji='H'";
			$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
			$resv->setFetchMode(PDO::FETCH_ASSOC);
			$barv = $resv->fetch();	
				if($barv['jumlah']<1){
					exit("Warning:Belum ada periode gaji");
				}
			*/	
			
			//$loctugas = ($bar['subbagian']=='' ? $bar['lokasitugas'] : $bar['subbagian']);
			
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
				
			$str="insert into ".$dbname.".upload_absensi (kodeorg,tanggalabsen,karyawanid,absensi,sumber,flag,tanggalinput,userid) values ('".$subbagian."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','finger','0','".date('Y-m-d')."','".$_SESSION['standard']['userid']."'
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
			//$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".substr($loctugas,0,4)."'");
			
			if($optOrg[$lokasitugas]=="PABRIK")
			{
				
				$insentif=$insentifharikerja=$insentifharilibur=0;
			
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='1' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$gajipokok = @($optGaji[$karyawanid]/25);
				
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='63' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$insentifharikerja =$optGaji[$karyawanid];
				
				$optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='64' and tahun='".substr(tanggalsystem($tglAbsen),0,4)."'");
				$insentifharilibur =$optGaji[$karyawanid];
				
				
				// $optGaji = makeOption($dbname,'sdm_5gajipokok','karyawanid,jumlah',"karyawanid='".$karyawanid."' and idkomponen='1'");
				// $gajipokok = @($optGaji[$karyawanid]/25);
				
				$str = "select count(tanggal) as counttanggal from ".$dbname.".sdm_absensiht where tanggal='".tanggalsystemn($tglAbsen)."'
						and kodeorg='".$subbagian."'";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
				$counttanggal = $bar['counttanggal'];
				if($counttanggal==0)
				{
					$str="insert into ".$dbname.".sdm_absensiht (tanggal,kodeorg,periode,posting) values ('".tanggalsystem($tglAbsen)."','".$subbagian."','".substr(tanggalsystem($tglAbsen),0,4)."-".substr(tanggalsystem($tglAbsen),4,2)."','0')";
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
				
				/*
				$day = date('D', strtotime($tgl));
				if($day=='Sun')$libur=true; else $libur=false;
				// kamus hari libur
				$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."'";
				$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
				$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
				while($roworg=$queorg->fetch()){
					if($roworg['keterangan']=='libur')$libur=true;
					if($roworg['keterangan']=='masuk')$libur=false;
				}
				if($libur==true){
					$hk=0;
					$gajipokok=0;
				}else{
					$hk=1;
					$gajipokok=$gajipokok;
				}
				
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,absensi,catu,fingerprint,hk,umr) values ('".$subbagian."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','1','1','1','".$gajipokok."')";
				try
				{
					$owlPDO->exec($str); 
				}
				catch (PDOException $e)
				{
					echo "DB Error : ".$e->getMessage();
					die();
				}
				*/
				
				
				
				
				/*
				
				$day = date('D', strtotime($tgl));
				if($day=='Sun')$libur=true; else $libur=false;
				// kamus hari libur
				$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."'  and (kebun='GLOBAL' or kebun='".$lokasitugas."')";
				$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
				$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
				while($roworg=$queorg->fetch()){
					if($roworg['keterangan']=='libur')$libur=true;
					if($roworg['keterangan']=='masuk')$libur=false;
				}
				if($libur==true){
					$hk=0;
					$gajipokok=0;
					$insentifharikerja=0;
				}else{
					$hk=1;
					$gajipokok=$gajipokok;
					$insentifharilibur=0;
				}
				
				
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,absensi,catu,fingerprint,hk,umr,insentif,insentiflibur) 
					values ('".$subbagian."','".tanggalsystem($tglAbsen)."','".$karyawanid."','H','1','1','1','".$gajipokok."','".$insentifharikerja."','".$insentifharilibur."')";
				try
				{
					$owlPDO->exec($str); 
				}
				catch (PDOException $e)
				{
					echo "DB Error : ".$e->getMessage();
					die();
				}
				
				*/
				
				
				
				$day = date('D', strtotime($tgl));
				if($day=='Sun'){
					$libur=true;
					$absensi='MG';
				}else{
					 $libur=false;
					 $absensi='H';
				} 
				// kamus hari libur
				$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal = '".$tgl."'  and (kebun='GLOBAL' or kebun='".$lokasitugas."')";
				$queorg=$owlPDO->query($strorg) or die(print " Gagal: ".PDOException::getMessage());
				$queorg->setFetchMode(PDO::FETCH_ASSOC);                   
				while($roworg=$queorg->fetch()){
					if($roworg['keterangan']=='libur'){
						$libur=true;
						$day = date('D', strtotime($tgl));
						if($day=='Sun'){
							$absensi='MG';
						}else{
							$absensi='HL';
						}
					}
					if($roworg['keterangan']=='masuk'){
						$libur=false;
						$absensi='H';
					}
				}
				$premi=0;
				if($libur==true){
					$hk=0;
					$insentifharikerja=0;
				}else{
					$hk=1;
					$premi=0;
					$insentifharilibur=0;
				}
				
				
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,absensi,catu,fingerprint,hk,umr,premi,insentif,insentiflibur) values 
														('".$subbagian."','".tanggalsystem($tglAbsen)."','".$karyawanid."','".$absensi."','1','1','".$hk."','".$gajipokok."','".$premi."','".$insentifharikerja."','".$insentifharilibur."')";
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
		
		if(substr($kdOrg,0,4) == 'BP9M')
		{
			$username='';
			$password='';
			$driver='TEST2';
			$dbName="/opt/absen2/att2000.mdb";

			if (!file_exists($dbName)) {
			   die("Could not find database file.");
			}
			
			try {
				$db = new PDO("odbc:$driver",'','');
			}
			catch (PDOException $e) {
				print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			
			// echo $tanggal;
			
			// $tanggal = tanggalsystem($tglAbsen);
			
			$tglSkrg = substr($tanggal,4,2)."/".substr($tanggal,6,2)."/".substr($tanggal,2,2);
			
			$str  = "select * from CHECKINOUT";
			$result = $db->query($str);
			while ($row = $result->fetch()) 
			{
				if(substr($row['CHECKTIME'],0,8) == $tglSkrg)
				{
					$arrAbs[$row['USERID']] = $row['USERID'];
				}
			}
			
			foreach($arrAbs as $val)
			{
				$str=$result=$row="";
				$str  = "select * from USERINFO where USERID=$val";
				$result = $db->query($str);
				$row = $result->fetch();
				if($row['Badgenumber']!='')
				{
					$arrNik[$row['Badgenumber']] = $row['Badgenumber'];
				}
			}
			
			if(count($arrNik)>0)
			{
				echo"<button class=mybutton id='btnupload2' onclick=uploaddata('".count($arrNik)."') id=btnupload>".$_SESSION['lang']['startUpload']."</button><p>";
			
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
				$no = 0;
				foreach($arrNik as $val){
					$OptNama = makeOption($dbname,'datakaryawan','nik,namakaryawan',"nik='".$val."'");
					if($OptNama[$val]!='')
					{
						$no+=1;
						echo"<tr class=rowcontent id='trabsen_".$no."'>
							<td style='text-align:right'>".$no."</td>
							<td id='tdabsen_".$no."' style='text-align:center'>".$val."</td>
							<td>".$OptNama[$val]."</td>
							<td>".tanggalnormal($tanggal)."</td>
						</tr>";
					}
				}
			}else{
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
					
					echo"<tr class=rowcontent align=center>
						<td colspan=4 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
					</tr>";
			}
			echo"</tbody></table>";
		
		}
		else
		{
			
			$str="select * from ".$dbname.".sdm_5ipfinger where kodeorg='".$kdOrg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$numrows=$res->rowCount();
			if($numrows<=0){
				exit("warning: Silahkan setup ip fingerprint di menu sdm->setup->ip finger.");
			}else{
				$bar=$res->fetch();
				$ipAdd=$bar['ip'];
				//$ipAdd='192.173.1.112';
				$dbnm=$bar['dbname'];
				$tblnm=$bar['tblname'];
				$usrName=$bar['username'];
				$pswrd=$bar['password'];
				$prt=$bar['port'];
				
				//@$conn=mysql_connect($ipAdd.":".$prt,$usrName,$pswrd) or die("Error/Gagal :Unable to Connect to database : ".$ipAdd);
				
				try 
				{
					$owlPDOS = new PDO('mysql:host='.$ipAdd.';dbname='.$dbnm, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => false));
					$owlPDOS->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				}
				catch (PDOException $e) 
				{
					print " Gagal, could not connect\n";	
					print "Error!: " . $e->getMessage() . "<br/>";
					die();
				}
				// @$conn=mysql_connect($ipAdd.":".$prt,$usrName,$pswrd) or die("Komputer fingerscan tidak terkoneksi");
				$sColom="SHOW COLUMNS FROM ".$dbnm.".".$tblnm."";
				
				$i = 0;
				$tColom=array();
				$tmpCol ="";
				
				$qColom=$owlPDOS->query($sColom) or die(print " Gagal: ".PDOException::getMessage());
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
				$sCob="select b.nik,a.scan_date,b.first_name,b.last_name
				from ".$dbnm.".".$tblnm." a
				left join ".$dbnm.".emp b on a.pin = b.pin
				where a.scan_date like '".$tanggal."%' and b.nik != '' group by a.pin order by b.first_name asc, b.last_name asc";
				$res=$owlPDOS->query($sCob) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_NUM);
				$row=$res->rowCount();
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
					while($hsl=$res->fetch()){	
						$OptNama = makeOption($dbname,'datakaryawan','nik,namakaryawan',"nik='".$hsl[0]."'");
						// if($OptNama[$hsl[0]]!='')
						// {
							$no+=1;
							echo"<tr class=rowcontent id='trabsen_".$no."'>
								<td style='text-align:right'>".$no."</td>
								<td id='tdabsen_".$no."'>".$hsl[0]."</td>
								<td>".$OptNama[$hsl[0]]."</td>
								<td>".tanggalnormal(substr($hsl[1],0,10))."</td>
							</tr>";
						//}
					}
				}else{
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
	}
?>