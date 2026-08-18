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
	$selisih=checkPostGet('selisih','');
	$jammasuk=checkPostGet('jammasuk','');
	$jamkeluar=checkPostGet('jamkeluar','');
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe');
	
	switch($method){
		case 'loaddata':
			getContainer($kdOrg,tanggalsystem($tglAbsen));
		break;
		
		case 'preview2':
			$str="select * from ".$dbname.".sdm_5ipfinger where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
			$res=fetchData($str);
			$ip = $res[0]['ip'];
			
			$opts = array(
				'http'=>array(
					'method'=>"GET",
					'header'=>"Accept-language: en\r\nCookie: foo=bar\r\n"
				)
			);
			$context = stream_context_create($opts);
			for($i=1;$i<=1000;$i++){
				$number.=($i.",");
			}
			
			// Open the file using the HTTP headers set above
			$file = file_get_contents("http://".$ip."/form/Download?uid=".$number."&sdate=".tanggalsystemn($tglAbsen)."&edate=".tanggalsystemn($tglAbsen), false, $context);
			
			$data = array();
			$record = explode("\n",$file);
			$isitemp = array();
			foreach($record as $r){
				$r = str_replace("\t"," ",$r);
				if($isitemp[$r] != $r){
					$isi = explode(" ",$r);
					array_push($data, $isi);
				}
				$isitemp[$r] = $r;
			}
			
			$arrNik = array();
			foreach($data as $key=>$val){
				// if($val[2]=='F' or $val[2]=='K'){
					$arrNik[substr($val[0],1)] = substr($val[0],1);
					if(substr($val[0],0,1)=='1'){
						$arrCount[substr($val[0],1)]['F']+=1;
					}else{
						$arrCount[substr($val[0],1)]['K']+=1;
					}
					$jlhdata = (count($val)-3);
					$arrTime[substr($val[0],1)][$val[$jlhdata]]=$val[$jlhdata];
				// }
			}
			
			// echo"<pre>";
			// print_r($arrCount);
			// print_r($record);
			// print_r($data);
			// echo"</pre>";
			
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
							<td style='text-align:center'>Finger</td>
							<td style='text-align:center'>Card</td>
							<td style='text-align:center'>".$_SESSION['lang']['selisih']."</td>
							<td style='text-align:center'>".$_SESSION['lang']['jammasuk']."</td>
							<td style='text-align:center'>".$_SESSION['lang']['jamkeluar']."</td>
							<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
						</tr>
						</thead>
						<tbody>";
				$no = 0;
				foreach($arrNik as $val){
					$OptNama = makeOption($dbname,'datakaryawan','nik,namakaryawan',"nik='".$val."'");
					$OptKarId = makeOption($dbname,'datakaryawan','nik,karyawanid',"nik='".$val."'");
					$karyawanid = $OptKarId[$val];
					if($OptNama[$val]!='')
					{
						$no+=1;
						$countfinger = ($arrCount[$val]['F']==''?0:$arrCount[$val]['F']);
						$countcard = ($arrCount[$val]['K']==''?0:$arrCount[$val]['K']);
						arsort($arrTime[$val]);
						foreach($arrTime[$val] as $keyx=>$valx){
							$jammasuk = $keyx;
						}
						asort($arrTime[$val]);
						foreach($arrTime[$val] as $keyx=>$valx){
							$jamkeluar = $keyx;
						}
						$selisih = $countfinger-$countcard;
						
						$status = "";
						$str="select waktuabsen from ".$dbname.".sdm_5waktuabsen where karyawanid='".$karyawanid."'";
						$res=fetchdata($str);
						$waktuabsen = $res[0]['waktuabsen'];
						$countWktAbsen = count($res);
						if($countWktAbsen < 1){
							$status = "Waktu absen belum ada";
						}else{
							$str="select * from ".$dbname.".sdm_5stdwaktuabsen where id='".$waktuabsen."'";
							$res=fetchData($str);
							$waktumasuk = addZero($res[0]['jammasuk'],2)."".addZero($res[0]['menitmasuk'],2);
							$waktukeluar = addZero($res[0]['jamkeluar'],2)."".addZero($res[0]['menitkeluar'],2);
							$expMasuk = explode(":",$jammasuk);
							$expKeluar = explode(":",$jamkeluar);
							$waktumasuk2 = addZero($expMasuk[0],2)."".addZero($expMasuk[1],2);
							$waktukeluar2 = addZero($expKeluar[0],2)."".addZero($expKeluar[1],2);
							if($waktumasuk < $waktumasuk2 && $waktukeluar > $waktukeluar2){
								$status="Terlambat & Pulang Awal";
							}else if($waktumasuk < $waktumasuk2){
								$status="Terlambat";
							}else if($waktukeluar > $waktukeluar2){
								$status="Pulang Awal";
							}
						}
						
						echo"<tr class=rowcontent id='trabsen_".$no."'>
							<td style='text-align:right'>".$no."</td>
							<td id='tdabsen_".$no."' style='text-align:center'>".$val."</td>
							<td>".$OptNama[$val]."</td>
							<td>".$tglAbsen."</td>
							<td align='center'>".$countfinger."</td>
							<td align='center'>".$countcard."</td>
							<td id='selisih_".$no."' align='center' style='background-color:".($selisih!=0?'red':'')."'>".$selisih."</td>
							<td id='jammasuk_".$no."' align='center'>".$jammasuk."</td>
							<td id='jamkeluar_".$no."' align='center'>".$jamkeluar."</td>
							<td align='center'>".$status."</td>
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
						<td style='text-align:center'>Finger</td>
						<td style='text-align:center'>Card</td>
						<td style='text-align:center'>".$_SESSION['lang']['selisih']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['jammasuk']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['jamkeluar']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
					</tr>
					</thead>
					<tbody>";
					
					echo"<tr class=rowcontent align=center>
						<td colspan=10 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
					</tr>";
			}
			echo"</tbody></table>";
		break;
		
		case 'insert':
			// if($kdOrg==''){
				// echo "Gagal : Kode Organisasi harus dipilih.";
				// exit();
			// }
			if($tglAbsen==''){
				echo "Gagal : Tanggal harus diisi.";
				exit();
			}
			if($krywnId==''){
				echo "Gagal : Nama Karyawan harus dipilih.";
				exit();
			}
			
			$str="select * from ".$dbname.".upload_absensiho where tanggalabsen='".tanggalsystem($tglAbsen)."' and karyawanid='".$krywnId."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=$qry->rowCount();
			if($numrows>=1){
				echo "Error: Absen sudah pernah diinput sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".upload_absensiho (kodeorg,tanggalabsen,karyawanid,absensi,sumber,flag,tanggalinput,userid,noba) 
				values ('','".tanggalsystem($tglAbsen)."','".$krywnId."','".$absniId."','manual','0','".date('Y-m-d')."','".$_SESSION['standard']['userid']."','".$noba."')";
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
			$str="delete from ".$dbname.".upload_absensiho where kodeorg='".$kdOrg."' and tanggalabsen='".tanggalsystem($tglAbsen)."' and karyawanid='".$krywnId."'";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'uploaddata':
			$expperiode = explode('-',$tglAbsen);
			$periode = $expperiode[2]."-".$expperiode[1];
			$tahun = $expperiode[2];
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
			
			## Get Gaji Pokok
			$umr=0;
			$gajipokok=0;
			$penaltykehadiran=0;
			$str="select * from ".$dbname.".sdm_5gajipokokho where karyawanid='".$karyawanid."' and tahun='".$tahun."'";
			$res=fetchData($str);
			foreach($res as $key=>$val){
				if($val['idkomponen']=='1'){
					$gajipokok = $val['jumlah'];
					@$umr=$val['jumlah']/25;
				}
				if($val['idkomponen']=='74'){
					$penaltykehadiran = $val['jumlah'];
				}
			}
			$OptNama = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawanid."'");
			if($gajipokok=='0'||$gajipokok==''){
				// exit("Warning : Setup Gaji Pokok belum ada untuk karyawan ".$OptNama[$karyawanid]);
			}
			
			if($penaltykehadiran=='0'||$penaltykehadiran==''){
				// exit("Warning : Setup Denda Absensi belum ada untuk karyawan ".$OptNama[$karyawanid]);
			}
			
			##Pengurangan Cuti
			$status = "";
			$jlhmenit = 0;
			$str="select waktuabsen from ".$dbname.".sdm_5waktuabsen where karyawanid='".$karyawanid."'";
			$res=fetchdata($str);
			$waktuabsen = $res[0]['waktuabsen'];
			$countWktAbsen = count($res);
			if($countWktAbsen < 1){
				// exit("Warning : Waktu absen belum ada untuk karyawan ".$OptNama[$karyawanid]);
			}else{
				$str="select * from ".$dbname.".sdm_5stdwaktuabsen where id='".$waktuabsen."'";
				$res=fetchData($str);
				$waktumasuk = addZero($res[0]['jammasuk'],2)."".addZero($res[0]['menitmasuk'],2);
				$waktukeluar = addZero($res[0]['jamkeluar'],2)."".addZero($res[0]['menitkeluar'],2);
				$expMasuk = explode(":",$jammasuk);
				$expKeluar = explode(":",$jamkeluar);
				$waktumasuk2 = addZero($expMasuk[0],2)."".addZero($expMasuk[1],2);
				$waktukeluar2 = addZero($expKeluar[0],2)."".addZero($expKeluar[1],2);
				$jm = $res[0]['jammasuk'];
				$mm = $res[0]['menitmasuk'];
				$jm2 = $expMasuk[0];
				$mm2 = $expMasuk[1];
				$jk = $res[0]['jamkeluar'];
				$mk = $res[0]['menitkeluar'];
				$jk2 = $expKeluar[0];
				$mk2 = $expKeluar[1];
				if($waktumasuk < $waktumasuk2 && $waktukeluar > $waktukeluar2){
					$jlhmenit = (($jm2-$jm)*60) + ($mm2-$mm) + (($jk-$jk2)*60) + ($mk-$mk2);
				}else if($waktumasuk < $waktumasuk2){
					$jlhmenit = (($jm2-$jm)*60) + ($mm2-$mm);
				}else if($waktukeluar > $waktukeluar2){
					$jlhmenit = (($jk-$jk2)*60) + ($mk-$mk2);
				}
			}
			$potCuti = $jlhmenit / (8*60);			
			
			$str="select * from ".$dbname.".sdm_absensiht where tanggal='".tanggalsystem($tglAbsen)."' and kodeorg='".$lokasitugas."'";
			$res=fetchData($str);
			$countht = count($res);
			
			if($countht<=0){
				$str="insert into ".$dbname.".sdm_absensiht values ('".tanggalsystem($tglAbsen)."','".$lokasitugas."','','".$periode."','0','','')";
				try{$owlPDO->exec($str);}catch(PDOException $e){echo "DB Error : ".$e->getMessage();die();}
			}
			
			$str="select * from ".$dbname.".sdm_absensidt where tanggal='".tanggalsystem($tglAbsen)."' and kodeorg='".$lokasitugas."' and karyawanid='".$karyawanid."'";
			$res=fetchData($str);
			$countdt = count($res);
			
			if($countdt<=0){
				$str="insert into ".$dbname.".sdm_absensidt (kodeorg,tanggal,karyawanid,shift,absensi,jam,jamPlg,jamistirahatdari,jamistirahatsampai,penjelasan,catu,penaltykehadiran,premi,insentif,insentiflibur,fingerprint,hk,umr,tunjangan) values ('".$lokasitugas."','".tanggalsystem($tglAbsen)."','".$karyawanid."','','H','".$jamkeluar."','".$jamkeluar."','00:00:00','00:00:00','','1','".$penaltykehadiran."','0','0','0','1','1','".$umr."','0')";
				try{
					$owlPDO->exec($str);
					
					if($potCuti > 0){
						$str="insert into ".$dbname.".sdm_cutidt values ('".$karyawanid."','".$lokasitugas."','".$tahun."','".tanggalsystem($tglAbsen)."','".tanggalsystem($tglAbsen)."','".$potCuti."','Potongan cuti atas absensi')";
						try{$owlPDO->exec($str);}catch(PDOException $e){echo "DB Error : ".$e->getMessage();die();}
					}
				}catch(PDOException $e){echo "DB Error : ".$e->getMessage();die();}
			}
		break;
		
		default:
        break;	
	}
	
	function getContainer($kdOrg,$tanggal){
		global $owlPDO;
		global $dbname;
		
		$str="select a.*,b.namakaryawan,c.keterangan,d.namaorganisasi from ".$dbname.".upload_absensiho a 
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
					<td style='display:none'>".$res->kodeorg." - ".$res->namaorganisasi."</td>
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
?>